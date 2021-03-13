<?php

namespace Magepow\AutoRelatedProduct\Block\Adminhtml\Rpr\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class Ruleinfo extends Generic implements TabInterface
{
    protected $_store;

    protected $_customer;
    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        \Magento\Store\Model\System\Store $store,
        \Magento\Customer\Ui\Component\Listing\Column\Group\Options $customer,
        array $data = []
    ) {
        $this->_customer = $customer;
        $this->_store = $store;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Rule Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Rule Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Generic
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('autorelatedproduct');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Rule Name'), 'title' => __('Rule Name'), 'required' => true]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => ['1' => __('Active'), '0' => __('Inactive')]
            ]
        );

        $fieldset->addField(
            'priority',
            'text',
            [
                'label' => __('Priority'),
                'title' => __('Priority'),
                'name' => 'priority',
                'note'  => __('Only one rule can be applied in one position. If there are several rules, the rule with the highest priority will be executed. Here 1 is the smallest priority.')
            ]
        );
        
        $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'store_id[]',
                    'label' => __('Stores'),
                    'title' => __('Stores'),
                    'required' => true,
                    'values' => $this->_store->getStoreValuesForForm(false, true)
                ]
        );

        $field = $fieldset->addField(
                'customer_group_id',
                'multiselect',
                [
                    'name' => 'customer_group_id[]',
                    'label' => __('Customer Groups'),
                    'title' => __('Customer Groups'),
                    'required' => true,
                    'values' => $this->_customer->toOptionArray()
                ]
        );
        
        if (!$model->getId()) {
            $model->setData('status', '1');
            $model->setData('priority', '1');
        }

        $form->setValues($model->getData());

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

        $this->_eventManager->dispatch('adminhtml_rpr_edit_tab_main_prepare_form', ['form' => $form]);

        return parent::_prepareForm();
    }
}