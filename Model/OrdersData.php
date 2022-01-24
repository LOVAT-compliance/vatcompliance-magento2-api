<?php

namespace Lovat\Api\Model;

use Lovat\Api\Api\Data\OrdersDataInterface;
use Magento\Framework\DataObject;

class OrdersData extends DataObject implements OrdersDataInterface
{
    /**
     * @inheritDoc
     */
    public function getRemainingData()
    {
        return $this->getData(self::REMAINING_DATA);
    }

    public function getOrders()
    {
        return $this->getData(self::ORDERS);
    }

    public function setRemainingData(array $remainingData)
    {
        return $this->setData(self::REMAINING_DATA, $remainingData);
    }

    public function setOrders(array $orders)
    {
        return $this->setData(self::ORDERS, $orders);
    }
}
