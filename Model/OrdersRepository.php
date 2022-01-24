<?php
namespace Lovat\Api\Model;

use Lovat\Api\Api\Data\OrdersDataContainerInterface;
use Lovat\Api\Api\Data\OrdersDataContainerInterfaceFactory;
use Lovat\Api\Api\Data\OrdersDataInterface;
use Lovat\Api\Api\Data\OrdersDataInterfaceFactory;
use Lovat\Api\Helper\Data;
use Lovat\Api\Model\ResourceModel\Orders as OrdersResource;
use Magento\Framework\Webapi\Exception as Exception;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;

class OrdersRepository
{
	const LOG_EMPTY = 'empty_data';

	const LOG_DATA_SENT = 'data_sent';

	const COUNT_PAGE_SIZE = 5000;

	/**
	 * @var OrdersResource
	 */
	private $ordersResource;

	protected $orderCollectionFactory;

	/**
	 * @var OrdersDataContainerInterfaceFactory
	 */
	protected $ordersDataContainerFactory;

	/**
	 * @var OrdersDataInterfaceFactory
	 */
	protected $ordersDataFactory;

	public function __construct(
		Data $helper,
		OrderCollection $orderCollectionFactory,
		OrdersResource $ordersResource,
		OrdersDataContainerInterfaceFactory $ordersDataContainerFactory,
		OrdersDataInterfaceFactory $ordersDataFactory
	) {
		$this->orderCollectionFactory = $orderCollectionFactory;
		$this->ordersResource = $ordersResource;
		$this->helper = $helper;
		$this->ordersDataContainerFactory = $ordersDataContainerFactory;
		$this->ordersDataFactory = $ordersDataFactory;
	}

	/**
	 * @param string $from
	 * @param string $to
	 * @param int $p
	 * @return OrdersDataContainerInterface
	 * @throws Exception
	 */

	public function get(string $from, string $to, int $p)
	{
		$result = $this->helper->validationApiGetDataFromTo($from, $to);
		if ($result === false) {
			throw new Exception(
				__("Problem with data, please complete required parameters such as 'from' and 'to' or make sure the date format is correct"),
				400,
				Exception::HTTP_BAD_REQUEST
			);
		} else {
			$collection = $this->orderCollectionFactory->create()
				->addFieldToSelect(['increment_id', 'global_currency_code', 'status', 'updated_at', 'total_due', 'shipping_address_id', 'total_refunded', 'total_paid'])
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
				['billingTable' => 'sales_order_address'],
				"main_table.entity_id = billingTable.parent_id AND billingTable.address_type = 'billing'",
				['vat_id', 'country_id', 'city', 'address_type', 'telephone', 'region', 'firstname', 'lastname']
			);

			$collection->setPageSize(self::COUNT_PAGE_SIZE)
				->setCurPage($p);

			//if such pagination exists
			if (($collection->getSize() + self::COUNT_PAGE_SIZE) >= ($p * self::COUNT_PAGE_SIZE)) {
				$remainingData = $this->remainingAmount($result['from'], $result['to'], $p);
				$data = $collection->getData();

				$this->helper->saveLogData(self::LOG_DATA_SENT);

				$ordersDataContainer = $this->ordersDataContainerFactory->create();
				$ordersDataFactory = $this->ordersDataFactory->create();

				$ordersDataFactory->setRemainingData($remainingData);
				$ordersDataFactory->setOrders($data);
				$ordersDataContainer->setApiData($ordersDataFactory);

				return $ordersDataContainer;
			} else {
				$this->helper->saveLogData(self::LOG_EMPTY);
				throw new Exception(
					__("Could not find data for your request"),
					200,
					Exception::HTTP_NOT_FOUND
				);
			}
		}
	}

	public function remainingAmount(string $from, string $to, int $p)
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

		$returnArray[] = [
			OrdersDataInterface::REMAINING_DATA => $remainingData,
			'count' => $count,
			'limit' => self::COUNT_PAGE_SIZE,
			'offset' => $p
		];

		return $returnArray;
	}
}
