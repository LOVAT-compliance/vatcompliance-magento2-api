<?php

namespace Lovat\Api\Api\Data;

interface OrdersDataInterface
{
    const REMAINING_DATA = 'remaining_data';
    const ORDERS = 'orders';

    /**
     * @return int
     */
    public function getRemainingData();

    /**
     * @return int
     */
    public function getOrders();

    /**
     * @param array $remainingData
     * @return $this
     */
    public function setRemainingData(array $remainingData);

    /**
     * @param array $orders
     * @return $this
     */
    public function setOrders(array $orders);
}
