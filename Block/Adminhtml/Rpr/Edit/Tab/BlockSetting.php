<?php

namespace Magepow\AutoRelatedProduct\Block\Adminhtml\Rpr\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class BlockSetting extends Generic implements TabInterface
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
        return __('Block Settings');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Block Settings');
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
            'block_title',
            'text',
            ['name' => 'block_title', 'label' => __('Block Title'), 'title' => __('Block Title'), 'required' => false]
        );

        $fieldset->addField(
            'sort_by',
            'select',
            [
                'label' => __('Sort By'),
                'title' => __('Sort By'),
                'name' => 'sort_by',
                'required' => true,
                'options' => ['0' => __('Random'), '1' => __('Name'), '2' => __('Price: high to low'), '3' => __('Price: low to high'), '4' => __('Newest')]
            ]
        );

        $fieldset->addField(
            'product_limit',
            'text',
            [
                'label' => __('Max Products to Display'),
                'title' => __('Max Products to Display'),
                'required' => true,
                'name' => 'product_limit',
            ]
        );

        if (!$model->getId()) {
            $model->setData('product_limit', '10');
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