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

    /**
     * @inheritDoc
     */
    public function getOrders()
    {
        return $this->getData(self::ORDERS);
    }

    /**
     * @inheritDoc
     */
    public function getDepartureAddress()
    {
        return $this->getData(self::DEPARTURE_ADDRESS);
    }

    /**
     * @inheritDoc
     */
    public function setRemainingData(array $remainingData)
    {
        return $this->setData(self::REMAINING_DATA, $remainingData);
    }

    /**
     * @inheritDoc
     */
    public function setOrders(array $orders)
    {
        return $this->setData(self::ORDERS, $orders);
    }

    /**
     * @param array $departureAddress
     * @return OrdersData|mixed
     */
    public function setDepartureAddress(array $departureAddress)
    {
        return $this->setData(self::DEPARTURE_ADDRESS, $departureAddress);
    }
}
