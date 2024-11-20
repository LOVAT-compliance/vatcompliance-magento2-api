<?php

namespace Lovat\Api\Block\Adminhtml\Settings\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Form extends Generic
{
    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('settings_form');
        $this->setTitle(__('Lovat Settings'));
    }

    /**
     * Prepare form
     *
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        //get lovat settings data from registry
        $settingsData = $this->_coreRegistry->registry('lovat_api_settings_data');
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form', 'action' => $this->getData('action'),
                    'method' => 'post', 'enctype' => 'multipart/form-data'
                ]
            ]
        );

        $form->setHtmlIdPrefix('lovat_settings_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Lovat Settings'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'departure_zip',
            'text',
            [
                'name' => 'departure_zip',
                'label' => __('Departure zip'),
                'title' => __('departure_zip'),
                'required' => false,
                'value' => $settingsData['departure_zip'],
//                 'class' => 'validate-number',
//                 'maxlength' => '7'
            ]
        );

        $fieldset->addField(
            'departure_country',
            'select',
            [
                'name' => 'departure_country',
                'label' => __('Departure country'),
                'title' => __('departure_country'),
                'required' => true,
                'value' => $settingsData['departure_country'],
                'values' => $this->_coreRegistry->registry('lovat_api_settings_countries') //get countries from registry
            ]
        );

        $fieldset->addField(
            'api_key',
            'text',
            [
                'name' => 'api_key',
                'label' => __('Api key'),
                'title' => __('Api key'),
                'required' => true,
                'value' => $settingsData['api_key'],
                'maxlength' => 255,
            ]
        );

        $fieldset->addField(
            'calculate_tax',
            'checkbox',
            [
                'name' => 'calculate_tax',
                'label' => __('Calculate tax'),
                'title' => __('Calculate tax'),
                'value' => $settingsData['calculate_tax'],
                'checked' => $settingsData['calculate_tax'],
                'class' => 'admin__actions-switch-checkbox',
                'after_element_js' => '
                                    <label class="admin__actions-switch-label" for="lovat_settings_calculate_tax">
                                        <span class="admin__actions-switch-text" data-text-on="' . __('Yes') . '" data-text-off="' . __('No') . '"></span>
                                    </label>
                                    <script type="text/javascript">
                                        require(["jquery"], function($){
                                            $("#lovat_settings_calculate_tax").change(function() {
                                                if($("#lovat_settings_calculate_tax").is(":checked")) {
                                                    $("#lovat_settings_calculate_tax").val("1");
                                                } else {
                                                    $("#lovat_settings_calculate_tax").val("0");
                                                }
                                            });
                                    
                                            $("#edit_form").submit(function() {
                                                $("#lovat_settings_calculate_tax").prop("checked", true);
                                            });
                                         });
                                    </script>
              ',
                'after_element_html' => __('<style>#lovat_settings_calculate_tax{display: none !important;}</style><p style="font-weight: 600; padding-top: 20px;">Please, click "Test connection" button to make sure the data entered is correct.</p>'),
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
