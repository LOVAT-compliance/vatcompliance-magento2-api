<?php

namespace Lovat\Api\Block\Adminhtml\Settings;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize staff grid edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Lovat_Api';
        $this->_controller = 'adminhtml_settings';

        parent::_construct();

        //add button to test api request
        $this->addButton(
            'test_lovat_api_connect',
            ['label' => __('Test connection'), 'onclick' => 'testApiSettings()', 'class' => 'test_lovat_api_connect']
        );

        if ($this->_isAllowedAction('Lovat_Api::save')) {
            $this->buttonList->update('save', 'label', __('Save settings'));
        } else {
            $this->buttonList->remove('save');
        }
    }

    /**
     * Retrieve text for header element depending on loaded blocklist
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('lovat_api')->getId()) {
            return __("Edit Staff '%1'", $this->escapeHtml($this->_coreRegistry->registry('lovat_api')->getTitle()));
        } else {
            return __('Log details');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('settings/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }

    /**
     * Prepare form Html. call the phtml file with form.
     *
     * @return string
     */
    public function getFormHtml()
    {
        // get the current form as html content.
        $html = parent::getFormHtml();
        //Append the phtml file after the form content.
        $html .= $this->setTemplate('Lovat_Api::settings/api_settings.phtml')->toHtml();
        return $html;
    }
}
