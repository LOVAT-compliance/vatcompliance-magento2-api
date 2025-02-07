<?php

namespace Lovat\Api\Api\Data;

interface OrdersDataInterface
{
    public const REMAINING_DATA = 'remaining_data';
    public const ORDERS = 'orders';
    public const DEPARTURE_ADDRESS = 'departure_address';

    /**
     *Get Remaining data
     *
     * @return mixed
     */
    public function getRemainingData();

    /**
     * Get Orders data
     *
     * @return mixed
     */
    public function getOrders();

    /**
     * Get departure address
     *
     * @return mixed
     */
    public function getDepartureAddress();

    /**
     * Set Remaining data
     *
     * @param array $remainingData
     * @return mixed
     */
    public function setRemainingData(array $remainingData);

    /**
     * Set departure address from settings
     *
     * @param array $departureAddress
     * @return mixed
     */
    public function setDepartureAddress(array $departureAddress);

    /**
     * Set Orders data
     *
     * @param array $orders
     * @return mixed
     */
    public function setOrders(array $orders);
}
