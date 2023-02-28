<?php

namespace Lovat\Api\Model;

use Lovat\Api\Model\Cache as LovatCache;

class Configuration
{
    const LOVAT_API_URL = 'https://merchant.vatcompliance.co/api/1/';
    const TAX_RATE_LIST = 'tax_rate_list';
    const SHIPPING_TAX_RATE = 'shipping_tax_rate';

    /**
     * @var $apiKey
     */
    private $apiKey;

    /**
     * @var $departureZip
     */
    private $departureZip;

    /**
     * @var $departureCountry
     */
    private $departureCountry;

    /**
     * @var $calculateTax
     */
    private $calculateTax;

    /**
     * @var LovatCache
     */
    protected $cache;

    /**
     * @var SettingsFactory
     */
    private $_settingsFactory;

    /**
     * Configuration constructor.
     * @param SettingsFactory $settingsFactory
     * @param Cache $cache
     */
    public function __construct(
        \Lovat\Api\Model\SettingsFactory $settingsFactory,
        LovatCache $cache
    )
    {
        $this->_settingsFactory = $settingsFactory;
        $this->cache = $cache;
        $this->getSettingsData();
    }

    /**
     * Get data from database or cache, set data to property
     */
    private function getSettingsData()
    {
        $settingsData = $this->cache->getCache();
        if (!empty($settingsData)) {
            $this->apiKey = $settingsData['api_key'];
            $this->departureZip = $settingsData['departure_zip'];
            $this->departureCountry = $settingsData['departure_country'];
            $this->calculateTax = $settingsData['calculate_tax'];
        } else {
            //get data from DB
            $settingsData = $this->_settingsFactory->create()->getCollection()->getFirstItem();
            if (!empty($settingsData)) {
                $this->apiKey = $settingsData['api_key'];
                $this->departureZip = $settingsData['departure_zip'];
                $this->departureCountry = $settingsData['departure_country'];
                $this->calculateTax = $settingsData['calculate_tax'];

                //save data to cache
                $this->cache->saveCache([
                    'api_key' => $this->apiKey,
                    'departure_zip' => $this->departureZip,
                    'departure_country' => $this->departureCountry,
                    'calculate_tax' => $this->calculateTax
                ]);
            }
        }
    }

    /**
     * Return api_key if exist or null
     *
     * @return mixed|null
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Return departure_zip value
     *
     * @return string|null
     */
    public function getDepartureZip()
    {
        return $this->departureZip;
    }

    /**
     * Return departure_country value
     *
     * @return mixed|null
     */
    public function getDepartureCountry()
    {
        return $this->departureCountry;
    }

    /**
     * Return calculate_tax value
     *
     * @return string|null
     */
    public function getCalculateTax()
    {
        return $this->calculateTax;
    }

    /**
     * Returns the base API url
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return self::LOVAT_API_URL;
    }

    /**
     * Return tax rate api url
     *
     * @return string
     */
    public function getTaxRateApiUrl(): string
    {
        return self::LOVAT_API_URL . self::TAX_RATE_LIST . '/';
    }

    /**
     * Return shipping tax rate url
     *
     * @return string
     */
    public function getShippingTaxRateUrl(): string
    {
        return self::LOVAT_API_URL . self::SHIPPING_TAX_RATE . '/';
    }
}
