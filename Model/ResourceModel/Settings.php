<?php

namespace Lovat\Api\Model\ResourceModel;

class Settings extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const LOVAT_SETTINGS_TABLE_NAME = 'lovat_settings';

    /**
     * Settings constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * set table name
     */
    protected function _construct()
    {
        $this->_init(self::LOVAT_SETTINGS_TABLE_NAME, 'id');
    }
}
