<?php
namespace Lovat\Api\Api;

interface OrdersRepositoryInterface
{
    /**
     * @param string $from
     * @param string $to
     * @param int $p
     * @return \Lovat\Api\Api\Data\OrdersDataContainerInterface
     */
    public function get(string $from, string $to, int $p = 1);
}
