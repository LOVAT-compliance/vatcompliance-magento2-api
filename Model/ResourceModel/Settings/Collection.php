<?php
namespace Lovat\Api\Model\ResourceModel\Settings;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'lovat_api_settings_collection';
    protected $_eventObject = 'settings_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lovat\Api\Model\Settings', 'Lovat\Api\Model\ResourceModel\Settings');
    }

}
