<?php

namespace Lovat\Api\Model;

class Settings extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'lovat_api_settings';
    protected $_cacheTag = 'lovat_api_settings';
    protected $_eventPrefix = 'lovat_api_settings';

    protected function _construct()
    {
        $this->_init('Lovat\Api\Model\ResourceModel\Settings');
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}
