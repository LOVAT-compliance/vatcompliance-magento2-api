<?php

namespace Lovat\Api\Model;

use Lovat\Api\Helper\Data;
use Lovat\Api\Model\Configuration as LovatConfig;

/**
 * Class Client
 * @package Lovat\Api\Model
 */
class Client
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var LovatConfig
     */
    protected $lovatConfig;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Data $helper
     * @param Configuration $lovatConfig
     */
    public function __construct(
        Data $helper,
        LovatConfig $lovatConfig
    ) {
        $this->helper = $helper;
        $this->lovatConfig = $lovatConfig;
        $this->apiKey = $this->lovatConfig->getApiKey();
    }

    /**
     * @param array $params
     * @return bool|int
     */
    public function testRequest(array $params)
    {
        $this->apiKey = $params[0]['api_key'];
        unset($params[0]['api_key']);
        return $this->doRequest($this->lovatConfig->getTaxRateApiUrl() . $this->apiKey, $params);
    }

    /**
     * Request to get tax rate
     *
     * @param $params
     * @return int
     */
    public function taxRate($params)
    {
        foreach ($params as $key => $param) {
            $params[$key] = $this->getDepartureParams($params[$key]);
        }

        return $this->doRequest($this->lovatConfig->getTaxRateApiUrl() . $this->apiKey, $params);
    }

    /**
     * Request to get shipping rate tax
     *
     * @param $params
     * @return bool|int
     */
    public function shippingRate($params)
    {
        $params = $this->getDepartureParams($params);
        return $this->doRequest($this->lovatConfig->getShippingTaxRateUrl() . $this->apiKey, $params);
    }

    /**
     * Do api request to vatcompliance to get vat
     *
     * @param $url
     * @param $params
     * @param string $method
     * @return integer|boolean
     */
    private function doRequest($url, array $params, $method = \Zend_Http_Client::POST)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-type: application/json',
        ]);
        $result = json_decode(curl_exec($ch));

        curl_close($ch);
        if (is_array($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Set departure params from DB
     *
     * @param $params
     * @return mixed
     */
    private function getDepartureParams($params)
    {
        $params['departure_country'] = $this->helper->convertCountry($this->lovatConfig->getDepartureCountry());
        $params['departure_zip'] = $this->lovatConfig->getDepartureZip();
        return $params;
    }
}
