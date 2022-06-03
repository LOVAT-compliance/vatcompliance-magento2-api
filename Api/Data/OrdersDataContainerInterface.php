<?php

namespace Lovat\Api\Api\Data;

interface OrdersDataContainerInterface
{
    /**
     * Get api Data
     *
     * @return OrdersDataInterface
     */
    public function getApiData();

    /**
     * Set Api data
     *
     * @param OrdersDataInterface $data
     * @return $this
     */
    public function setApiData(OrdersDataInterface $data);
}
