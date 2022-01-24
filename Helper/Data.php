<?php

namespace Lovat\Api\Helper;

use Lovat\Api\Model\Orders;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public function validationApiGetDataFromTo($from, $to)
    {
        if ($this->is_Date($from) != false and $this->is_Date($to) != false) {
            $from = new \DateTime($from);
            $to = new \DateTime($to);
            return [
                'from' => $from->format('Y-m-d h:i:s'),
                'to' => $to->format('Y-m-d h:i:s')
            ];
        }
        return false;
    }

    public function is_Date($str)
    {
        return strtotime($str);
    }

    public function saveLogData($status)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $model = $objectManager->create(Orders::class);
        $model->setStatus($status);
        if ($model->save()) {
            return true;
        }
        return false;
    }
}
