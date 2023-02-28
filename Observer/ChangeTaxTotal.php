<?php

namespace Lovat\Api\Observer;

use Lovat\Api\Helper\Data;
use Lovat\Api\Model\Client;
use Lovat\Api\Model\Configuration as LovatConfiguration;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

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
     * @param Data $helper
     * @param PriceCurrencyInterface $priceCurrency
     * @param JsonFactory $resultJsonFactory
     * @param Client $client
     * @param LovatConfiguration $lovatConfiguration
     */
    public function __construct(
        Data $helper,
        PriceCurrencyInterface $priceCurrency,
        JsonFactory $resultJsonFactory,
        Client $client,
        LovatConfiguration $lovatConfiguration
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->client = $client;
        $this->lovatConfiguration = $lovatConfiguration;
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

                //if exist country and zip -> calculate tax
                if (!empty($arrivalCountry) && !empty($arrivalZip)) {
                    $total = $observer->getTotal();
                    $currencyCode = $this->_priceCurrency->getCurrency()->getCurrencyCode();
                    $transactionDatetime = $this->helper->dateFormat(date('Y-m-d H:i:s'));

                    $params = [];
                    foreach ($items as $item) {
                        $params[] = [
                            'transaction_datetime' => $transactionDatetime,
                            'currency' => $currencyCode,
                            'transaction_sum' => $item->getRowTotalInclTax(),
                            'arrival_country' => $arrivalCountry,
                            'arrival_zip' => $arrivalZip,
                            'transaction_id' => $item->getProductId(),
                        ];
                    }

                    if (!empty($params)) {
                        $vatData = $this->client->taxRate($params);
                        $tax = 0;
                        if (!empty($vatData)) {
                            //set tax to items
                            foreach ($vatData as $data) {
                                foreach ($items as $item) {
                                    if ($item->getProductId() == $data->transaction_id) {
                                        $item->setTaxAmount($data->vat);
                                        $item->setTaxPercent($data->vat_percent);
                                        break;
                                    }
                                }
                                $tax = $tax + $data->vat;
                            }
                        }

                        //get shipping info
                        $shippingPrice = $shippingAddress->getShippingAmount();
                        $shippingParams = [
                            'arrival_country' => $arrivalCountry,
                            'arrival_zip' => $arrivalZip,
                            'currency' => $currencyCode,
                            'transaction_datetime' => $transactionDatetime,
                            'transaction_sum' => $shippingPrice
                        ];

                        //API request to vatcompliance get shipping tax info
                        $shippingRate = $this->client->shippingRate($shippingParams);
                        $shippingTax = 0;
                        if (!empty($shippingRate)) {
                            //Get shipping price
                            $shippingTax = $shippingRate[0]->vat;
                        }

                        $tax = $tax + $shippingTax;

                        //tax for quote
                        $quote->setTaxAmount($tax);
                        $quote->setBaseTaxAmount($tax);
                        $quote->setGrandTotal($quote->getGrandTotal() + $tax);
                        $quote->setBaseGrandTotal($quote->getBaseGrandTotal() + $tax);
                        $quote->setTotalsCollectedFlag(false);

                        //shipping address tax
                        $shippingAddress->setShippingTaxAmount($shippingTax);
                        $shippingAddress->setBaseShippingTaxAmount($shippingTax);
                        $shippingAddress->setTaxAmount($tax);
                        $shippingAddress->setBaseTaxAmount($tax);

                        //tax to total
                        $total->addTotalAmount('tax', $tax);
                        $total->addBaseTotalAmount('tax', $tax);
                        $total->setGrandTotal((float)$total->getGrandTotal() + $tax);
                        $total->setBaseGrandTotal((float)$total->getBaseGrandTotal() + $tax);
                    }
                }
            }
        }

        return $this;
    }
}
