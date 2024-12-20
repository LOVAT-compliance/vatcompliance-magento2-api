<?php

namespace Lovat\Api\Model;

use Lovat\Api\Api\Data\OrdersDataContainerInterface;
use Lovat\Api\Api\Data\OrdersDataContainerInterfaceFactory;
use Lovat\Api\Api\Data\OrdersDataInterface;
use Lovat\Api\Api\Data\OrdersDataInterfaceFactory;
use Lovat\Api\Helper\Data;
use Lovat\Api\Model\Configuration as LovatConfiguration;
use Lovat\Api\Model\ResourceModel\Orders as OrdersResource;
use Magento\Framework\Webapi\Exception as ErrorException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;

class OrdersRepository
{
    public const LOG_EMPTY = 'empty_data';

    public const LOG_DATA_SENT = 'data_sent';

    public const COUNT_PAGE_SIZE = 5000;

    /**
     * @var OrdersResource
     */
    private $ordersResource;

    /**
     * @var OrderCollection
     */
    protected $orderCollectionFactory;

    /**
     * @var OrdersDataContainerInterfaceFactory
     */
    protected $ordersDataContainerFactory;

    /**
     * @var OrdersDataInterfaceFactory
     */
    protected $ordersDataFactory;

    /**
     * @var LovatConfiguration
     */
    protected $lovatConfiguration;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Construct
     *
     * @param Data $helper
     * @param OrderCollection $orderCollectionFactory
     * @param OrdersResource $ordersResource
     * @param OrdersDataContainerInterfaceFactory $ordersDataContainerFactory
     * @param OrdersDataInterfaceFactory $ordersDataFactory
     * @param Configuration $lovatConfiguration
     */
    public function __construct(
        Data $helper,
        OrderCollection $orderCollectionFactory,
        OrdersResource $ordersResource,
        OrdersDataContainerInterfaceFactory $ordersDataContainerFactory,
        OrdersDataInterfaceFactory $ordersDataFactory,
        LovatConfiguration $lovatConfiguration
    ) {
        $this->helper = $helper;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->ordersResource = $ordersResource;
        $this->ordersDataContainerFactory = $ordersDataContainerFactory;
        $this->ordersDataFactory = $ordersDataFactory;
        $this->lovatConfiguration = $lovatConfiguration;
    }

    /**
     * Get params and return answer
     *
     * @param string $from
     * @param string $to
     * @param int $p
     * @return OrdersDataContainerInterface
     * @throws ErrorException
     * @throws \Exception
     */
    public function get(string $from, string $to, int $p): OrdersDataContainerInterface
    {
        $result = $this->helper->validationApiGetDataFromTo($from, $to);
        if ($result === false) {
            throw new ErrorException(
                __("Problem with data, please complete required parameters such as
                    'from' and 'to' or make sure the date format is correct"),
                400,
                ErrorException::HTTP_BAD_REQUEST
            );
        }

        $collection = $this->orderCollectionFactory->create()
            ->addFieldToSelect([
                'increment_id',
                'global_currency_code',
                'status',
                'updated_at',
                'total_due',
                'shipping_address_id',
                'total_refunded',
                'total_paid',
                'shipping_amount',
                'shipping_refunded'
            ])
            ->addAttributeToFilter('status', ['in' => [
                Order::STATE_CLOSED,
                Order::STATE_COMPLETE,
            ]])
            ->addAttributeToFilter('main_table.created_at', ['from' => $result['from'], 'to' => $result['to']]);

        $collection->getSelect()->joinLeft(
            ['orderItem' => 'sales_order_item'],
            "main_table.entity_id = orderItem.order_id",
            ['tax_amount', 'tax_percent']
        );

        $collection->getSelect()->joinLeft(
            ['shippingTable' => 'sales_order_address'],
            "main_table.entity_id = shippingTable.parent_id AND shippingTable.address_type = 'shipping'",
            ['vat_id', 'country_id', 'city', 'address_type', 'telephone', 'region', 'firstname', 'lastname']
        );


        $collection->setPageSize(self::COUNT_PAGE_SIZE)
            ->setCurPage($p);

        if (($collection->getSize() + self::COUNT_PAGE_SIZE) >= ($p * self::COUNT_PAGE_SIZE)) {
            $remainingData = $this->remainingAmount($result['from'], $result['to'], $p);
            $data = $collection->getData();

            $this->helper->saveLogData(self::LOG_DATA_SENT);

            $ordersDataContainer = $this->ordersDataContainerFactory->create();
            $ordersDataFactory = $this->ordersDataFactory->create();

            $departureAddress[] = [
                'departure_country' => $this->helper->convertCountry($this->lovatConfiguration->getDepartureCountry()),
                'departure_zip' => $this->lovatConfiguration->getDepartureZip()
            ];
            $ordersDataFactory->setDepartureAddress($departureAddress);
            $ordersDataFactory->setRemainingData($remainingData);
            $ordersDataFactory->setOrders($data);
            $ordersDataContainer->setApiData($ordersDataFactory);

            return $ordersDataContainer;
        } else {
            $this->helper->saveLogData(self::LOG_EMPTY);
            throw new ErrorException(
                __("Could not find data for your request"),
                200,
                ErrorException::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Get remaining amount
     *
     * @param string $from
     * @param string $to
     * @param int $p
     * @return array
     */
    public function remainingAmount(string $from, string $to, int $p): array
    {
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToSelect(['entity_id'])
            ->addAttributeToFilter('status', ['in' => [
                Order::STATE_CLOSED,
                Order::STATE_COMPLETE,
            ]])
            ->addAttributeToFilter('main_table.created_at', ['from' => $from, 'to' => $to]);

        $count = $collection->count();
        $remainingData = $count - ($p * self::COUNT_PAGE_SIZE);

        if ($remainingData < 0) {
            $remainingData = 0;
        }

        return [
            [
                OrdersDataInterface::REMAINING_DATA => $remainingData,
                'count' => $count,
                'limit' => self::COUNT_PAGE_SIZE,
                'offset' => $p
            ]
        ];
    }
}
