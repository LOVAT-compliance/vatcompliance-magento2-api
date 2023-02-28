<?php

namespace Lovat\Api\Controller\Adminhtml\Settings;

use Lovat\Api\Model\Cache as LovatCache;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class Save extends Action
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var LovatCache
     */
    protected $cache;

    /**
     * @param Action\Context $context
     * @param LovatCache $cache
     */
    public function __construct(
        Action\Context $context,
        LovatCache $cache
    ) {
        $this->type = 'Lovat\Api\Model\Settings';
        $this->cache = $cache;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lovat_Api::save');
    }

    /**
     * Save data
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $request = $this->_objectManager->create($this->type)
            ->getCollection()
            ->getFirstItem();

        $settingsData = $request->getData();
        $postData = $this->getRequest()->getPostValue(); //data from FORM
        unset($postData['form_key']);

        if (!empty($settingsData)) {
            //update data if exists
            $model = $this->_objectManager
                ->create($this->type)
                ->load($settingsData['id'])
                ->addData($postData);
        } else {
            //save if it's new data
            $model = $this->_objectManager
                ->create($this->type)
                ->addData($postData);
        }

        if ($model->save()) {
            $this->cache->saveCache($postData); //clear cache
            $this->messageManager->addSuccess(__('Your settings data was successfully saved.'));
        } else {
            $this->messageManager->addError(__('An error occurred while saving data, please try again'));
        }

        return $this->_redirect('*/*/');
    }
}
