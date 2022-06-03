<?php

namespace Lovat\Api\Helper;

use DateTime;
use Exception;
use Lovat\Api\Model\Orders;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;

class Data extends AbstractHelper
{
    /**
     *  Validate date.
     *
     * @param string $from
     * @param string $to
     * @return array|bool
     * @throws Exception
     */
    public function validationApiGetDataFromTo($from, $to)
    {
        if ($this->isDate($from) != false && $this->isDate($to) != false) {
            $from = new DateTime($from);
            $to = new DateTime($to);
            return [
                'from' => $from->format('Y-m-d h:i:s'),
                'to' => $to->format('Y-m-d h:i:s')
            ];
        }
        return false;
    }

    /**
     * Check if is date
     *
     * @param string $str
     * @return false|int
     */
    public function isDate($str)
    {
        return strtotime($str);
    }

    /**
     * Save log data.
     *
     * @param string $status
     * @return bool
     */
    public function saveLogData($status)
    {
        $objectManager = ObjectManager::getInstance(); // Instance of object manager
        $model = $objectManager->create(Orders::class);
        $model->setStatus($status);
        if ($model->save()) {
            return true;
        }
        return false;
    }
}
