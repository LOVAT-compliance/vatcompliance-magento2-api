<?php

namespace Lovat\Api\Api\Data;

interface OrdersDataContainerInterface
{
    /**
     * @return \Lovat\Api\Api\Data\OrdersDataInterface
     */
    public function getApiData();

    /**
     * @param \Lovat\Api\Api\Data\OrdersDataInterface $data
     * @return $this
     */
    public function setApiData(OrdersDataInterface $data);
}
