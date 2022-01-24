<?php

namespace Lovat\Api\Model;

use Lovat\Api\Api\Data\OrdersDataContainerInterface;
use Lovat\Api\Api\Data\OrdersDataInterface;

class OrdersDataContainer implements OrdersDataContainerInterface
{
    /**
     * @var \Lovat\Api\Api\Data\OrdersDataInterface
     */
    protected $data;

    /**
     * @inheritDoc
     */
    public function getApiData()
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function setApiData(OrdersDataInterface $data)
    {
        $this->data = $data;
        return $this;
    }
}
