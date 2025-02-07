<?php
namespace Lovat\Api\Model\ResourceModel\Orders;

use Lovat\Api\Model\Orders;
use Lovat\Api\Model\ResourceModel\Orders as OrdersResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct() //@codingStandardsIgnoreLine
    {
        $this->_init(Orders::class, OrdersResource::class);
    }
}
