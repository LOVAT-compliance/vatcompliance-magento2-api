<?php

namespace Lovat\Api\Controller\Adminhtml\Settings;

use Lovat\Api\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Data $helper
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_ListingWizard::save');
    }

    /**
     * Init actions
     *
     * @return \Magento\Framework\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Lovat_Api::settings')
            ->addBreadcrumb(__('Lovat'), __('Lovat'))
            ->addBreadcrumb(__('Lovat Settings'), __('Lovat Settings'));

        return $resultPage;
    }

    /**
     * Edit Staff
     *
     * @return \Magento\Framework\View\Result\Page
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Lovat Settings'));

        //get countries
        $countries = $this->_objectManager->create('Lovat\Api\Helper\Data')->countries();
        $request = $this->_objectManager->create('Lovat\Api\Model\Settings')
            ->getCollection()
            ->getFirstItem();

        $settings = $request->getData();
        if (empty($settings)) {
            $settings['departure_zip'] = '';
            $settings['departure_country'] = '';
            $settings['api_key'] = '';
            $settings['calculate_tax'] = 0;
        }

        $this->_coreRegistry->register('lovat_api_settings_countries', $countries);
        $this->_coreRegistry->register('lovat_api_settings_data', $settings);

        return $resultPage;
    }
}
