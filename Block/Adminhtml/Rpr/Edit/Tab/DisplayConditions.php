<?php

namespace Magepow\AutoRelatedProduct\Block\Adminhtml\Rpr\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
class DisplayConditions extends Generic implements TabInterface
{
    protected $block_position;
    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;
    protected $display_to_Category;
    protected $layoutFactory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magepow\AutoRelatedProduct\Model\Options\BlockPosition $block_position,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magepow\AutoRelatedProduct\Model\Options\Category $display_to_Category,
        array $data = []
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->display_to_Category = $display_to_Category;
        $this->rendererFieldset = $rendererFieldset;
        $this->conditions = $conditions;
        $this->block_position = $block_position;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('\'Where to Display\' Conditions ');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('\'Where to Display\' Conditions ');
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
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('\'Where to Display\' Conditions')]);

        $fieldsetId = 'rule_conditions_fieldset'. uniqid();
        $formName = 'catalog_rule_form'. uniqid();

        $model = $this->_coreRegistry->registry('autorelatedproduct');

        $block_position = $fieldset->addField(
                'block_position',
                'select',
                [
                    'name' => 'block_position',
                    'label' => __('Block Position'),
                    'title' => __('Block Position'),
                    'required' => true,
                    'values' => $this->block_position->toOptionArray()
                ]
        );
        
        $field = $fieldset->addField(
            'display_to_category',
                'multiselect',
                [
                    'name' => 'display_to_category',
                    'label' => __('Specific Categories'),
                    'title' => __('Specific Categories'),
                    'values' => $this->display_to_Category->toOptionArray(),
                ]
        );

        $Display_Place_Parameters = $model->getData('display_place');
        $Display_Place_Parameters = @unserialize($Display_Place_Parameters);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $modelRule = $objectManager->get('Magento\CatalogWidget\Model\RuleFactory');
        $modelConditions = $modelRule->create();
        if (is_array($Display_Place_Parameters))
        {
            $modelConditions->loadPost($Display_Place_Parameters);
            $modelConditions->getConditions()->setJsFormObject($fieldsetId);
        }

        $newChildUrl = $this->getUrl(
            'magepow_autorelatedproduct/rpr/newConditionHtml/form/' . $fieldsetId,
            ['form_namespace' => $fieldsetId]
        );

        $renderer = $this->rendererFieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($fieldsetId);
        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' => __(
                    'Choose the conditions to define what products display block.'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions_display',
            'text',
            ['name' => 'conditions_display', 'label' => __('conditions_display'), 'title' => __('conditions_display'), 'data-form-parts' => $formName]
        )->setRule(
            $modelConditions
        )->setRenderer(
            $this->conditions
        );

        $form->setValues($model->getData());
        $this->setForm($form);
        
        $block_position->setAfterElementHtml(
            '
                <script type="text/javascript">
                require([
                    "jquery",
                    "uiRegistry"
                ],  function($, uiRegistry){
                        jQuery(document).ready(function($) {
                            var tree = $(".rule-tree").eq(1);
                            var displaycategory = $(".field-display_to_category");
                            tree.hide();
                            displaycategory.hide();
                            var label=$("#rule_block_position :selected").parent().attr("label");
                            switch (label){
                                case "Product Page":
                                    tree.show();
                                    displaycategory.hide();
                                    break;
                                case "Shopping Cart Page":
                                    tree.hide();
                                    displaycategory.hide();
                                    break;
                                case "Category Page":
                                    tree.hide();
                                    displaycategory.show();
                                    break;
                            }
                            $("#rule_block_position").change(function ()
                            {
                                var label=$("#rule_block_position :selected").parent().attr("label");
                                switch (label){
                                    case "Product Page":
                                        tree.show();
                                        displaycategory.hide();
                                        break;
                                    case "Shopping Cart Page":
                                        tree.hide();
                                        displaycategory.hide();
                                        break;
                                    case "Category Page":
                                        tree.hide();
                                        displaycategory.show();
                                        break;
                                }
                            });
                        });
                });
                </script>
            '
        );

        return parent::_prepareForm();
    }

    public function toHtml()
    {
        $html = parent::toHtml();
        $html = str_replace("conditions","conditions_display",$html);
        return $html;
    }
}