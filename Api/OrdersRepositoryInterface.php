<?php

namespace Lovat\Api\Api;

use Lovat\Api\Api\Data\OrdersDataContainerInterface;

interface OrdersRepositoryInterface
{
    /**
     * Order get GET params
     *
     * @param string $from
     * @param string $to
     * @param int $p
     * @return OrdersDataContainerInterface
     */
    public function get(string $from, string $to, int $p = 1);
}
