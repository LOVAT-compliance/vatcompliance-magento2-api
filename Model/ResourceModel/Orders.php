<?php

namespace Lovat\Api\Model\ResourceModel;

class Orders extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var string
     */
    const TABLE_NAME = 'lovat_api_log';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() //@codingStandardsIgnoreLine
    {
        $this->_init(self::TABLE_NAME, 'log_id');
    }
}
