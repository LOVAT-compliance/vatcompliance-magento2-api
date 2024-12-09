<?php

namespace Lovat\Api\Observer;

use Lovat\Api\Helper\Data;
use Lovat\Api\Model\Client;
use Lovat\Api\Model\Configuration as LovatConfiguration;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\CacheInterface;

class ChangeTaxTotal implements ObserverInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var LovatConfiguration
     */
    protected $lovatConfiguration;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * Cache keys
     */
    private $cacheKeyAddress = 'last_address_hash';
    private $cacheKeyTaxData = 'cached_tax_data';
    private $cacheLifetime = 3600; // 1 час

    /**
     * @param Data $helper
     * @param PriceCurrencyInterface $priceCurrency
     * @param JsonFactory $resultJsonFactory
     * @param Client $client
     * @param LovatConfiguration $lovatConfiguration
     * @param CacheInterface $cache
     */
    public function __construct(
        Data $helper,
        PriceCurrencyInterface $priceCurrency,
        JsonFactory $resultJsonFactory,
        Client $client,
        LovatConfiguration $lovatConfiguration,
        CacheInterface $cache
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->client = $client;
        $this->lovatConfiguration = $lovatConfiguration;
        $this->cache = $cache;
    }

    /**
     * Calculate tax used vatcompliance API
     *
     * @param Observer $observer
     * @return $this|void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $lovatConfiguration = $this->lovatConfiguration->getCalculateTax();
        if (!empty($lovatConfiguration) && $lovatConfiguration == 1) {
            $quote = $observer->getQuote();
            $items = $quote->getItemsCollection();
            if (!empty($items)) {
                $shippingAddress = $quote->getShippingAddress();
                $arrivalCountry = $this->helper->convertCountry($shippingAddress->getCountry());
                $arrivalZip = $shippingAddress->getPostcode();
                $vatId = $shippingAddress->getVatId();
                if (is_array($vatId)) {
                    $vatId = implode(', ', $vatId);
                } elseif (empty($vatId)) {
                    $vatId = 'none';
                }

                if (!empty($arrivalCountry) && $arrivalCountry === 'USA' && empty($arrivalZip)){
                    return $this;
                }

                // Check if address is valid
                if (!empty($arrivalCountry)){
                    $total = $observer->getTotal();
                    $currencyCode = $this->_priceCurrency->getCurrency()->getCurrencyCode();
                    $transactionDatetime = $this->helper->dateFormat(date('Y-m-d H:i:s'));

                    // Generate hash for current address
                    $currentAddressHash = $this->hashAddress($shippingAddress);
                    $cachedAddressHash = $this->cache->load($this->cacheKeyAddress);
                    $cachedTaxData = $this->cache->load($this->cacheKeyTaxData);

                    if ($cachedAddressHash === $currentAddressHash && $cachedTaxData) {
                        $vatData = @unserialize($cachedTaxData);
                        if (!$vatData || !is_array($vatData)) {
                            $vatData = [];
                        }
                        $this->applyTaxDataToQuote($quote, $vatData, $observer);
                        return;
                    }

                    // Build API request parameters
                    $params = [];
                    foreach ($items as $item) {
                        $transactionSum = $item->getRowTotal();
                        if ($transactionSum === null) {
                            continue;
                        }
                        $params[] = [
                            'transaction_datetime' => $transactionDatetime,
                            'currency' => $currencyCode,
                            'transaction_sum' => $transactionSum,
                            'arrival_country' => $arrivalCountry,
                            'arrival_zip' => $arrivalZip,
                            'transaction_id' => $item->getProductId(),
                            'vat_number_of_buyer' => $vatId
                        ];
                    }

                    if (!empty($params)) {
                        try {
                            $vatData = $this->client->taxRate($params);

                            if (!$vatData || !is_array($vatData)) {
                                $vatData = [];
                            }

                            $this->cache->save($currentAddressHash, $this->cacheKeyAddress, ['TAX_CACHE'], $this->cacheLifetime);
                            $this->cache->save(serialize($vatData), $this->cacheKeyTaxData, ['TAX_CACHE'], $this->cacheLifetime);

                            $this->applyTaxDataToQuote($quote, $vatData, $observer);
                        } catch (\Exception $e) {
                            // Ignoring exceptions here to avoid breaking checkout process
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Generate a hash for the shipping address
     */
    private function hashAddress($address)
    {
        $addressData = [
            is_array($address->getStreet()) ? implode(' ', $address->getStreet()) : $address->getStreet(),
            $address->getCity(),
            $address->getPostcode(),
            $address->getRegion(),
            $address->getCountryId(),
            !empty($address->getVatId()) ? $address->getVatId() : 'none',
        ];
        $addressData = array_map(function ($value) {
            return is_array($value) ? implode(', ', $value) : (string)$value;
        }, $addressData);
        $addressData = array_map('strval', $addressData);
        return hash('sha256', implode('|', $addressData));
    }

    /**
     * Apply tax data to the quote
     */
    private function applyTaxDataToQuote($quote, $vatData, $observer)
    {
        $tax = 0;
        $items = $quote->getItemsCollection();

        foreach ($vatData as $data) {
            foreach ($items as $item) {
                if ($item->getProductId() == $data->transaction_id) {
                    $item->setTaxAmount($data->tax_amount);
                    $item->setTaxPercent($data->tax_rate);
                    break;
                }
            }
            $tax += $data->tax_amount;
        }

        $quote->setTaxAmount($tax);
        $quote->setBaseTaxAmount($tax);
        $quote->setGrandTotal($quote->getGrandTotal() + $tax);
        $quote->setBaseGrandTotal($quote->getBaseGrandTotal() + $tax);
        $quote->setTotalsCollectedFlag(false);

        $total = $observer->getTotal();
        $total->addTotalAmount('tax', $tax);
        $total->addBaseTotalAmount('tax', $tax);
        $total->setGrandTotal((float)$total->getGrandTotal() + $tax);
        $total->setBaseGrandTotal((float)$total->getBaseGrandTotal() + $tax);
    }
}
