<?php

namespace Lovat\Api\Controller\Adminhtml\Settings;

use Lovat\Api\Helper\Data;
use Lovat\Api\Model\Client;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Ajax extends Action
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var bool $success
     */
    protected $success = false;

    /**
     * @param Data $helper
     * @param Action\Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param JsonFactory $resultJsonFactory
     * @param Client $client
     */
    public function __construct(
        Data $helper,
        Action\Context $context,
        PriceCurrencyInterface $priceCurrency,
        JsonFactory $resultJsonFactory,
        Client $client
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->client = $client;
        parent::__construct($context);
    }

    /**
     * Test request.
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Exception
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $params[] = [
            'transaction_datetime' => $this->helper->dateFormat(date('Y-m-d H:i:s')),
            'currency' => $this->_priceCurrency->getCurrency()->getCurrencyCode(),
            'transaction_sum' => '100', //set custom price only for test
            'arrival_country' => 'USA', //setCustom only for test
            'arrival_zip' => '10001', //setCustom only for test
            'departure_country' => $this->helper->convertCountry($this->getRequest()->getParam('country')),
            'departure_zip' => $this->getRequest()->getParam('zipCode'),
            'api_key' => $this->getRequest()->getParam('api_key'),
            'transaction_id' => 'test_api_request'
        ];

        $vat = $this->client->testRequest($params);
        if ($vat) {
            return $result->setData(['success' => true, 'message' => 'Connected was correctly.']);
        }

        return $result->setData(['success' => false, 'message' => 'Something was wrong. Please enter correctly data.']);
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lovat_Api::resource');
    }
}
