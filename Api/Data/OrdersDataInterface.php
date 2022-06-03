<?php

namespace Lovat\Api\Api\Data;

interface OrdersDataInterface
{
    public const REMAINING_DATA = 'remaining_data';
    public const ORDERS = 'orders';

    /**
     * Get Remaining data
     *
     * @return int
     */
    public function getRemainingData();

    /**
     * Get Orders data
     *
     * @return int
     */
    public function getOrders();

    /**
     * Set Remaining data
     *
     * @param array $remainingData
     * @return $this
     */
    public function setRemainingData(array $remainingData);

    /**
     * Set Orders data
     *
     * @param array $orders
     * @return $this
     */
    public function setOrders(array $orders);
}
