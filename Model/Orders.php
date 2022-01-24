<?php

namespace Lovat\Api\Model;

use Lovat\Api\Model\ResourceModel\Orders as OrdersResource;
use Magento\Framework\Model\AbstractModel;

class Orders extends AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct() //@codingStandardsIgnoreLine
    {
        $this->_init(OrdersResource::class);
    }
}
