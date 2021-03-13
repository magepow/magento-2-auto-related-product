<?php

namespace Magepow\AutoRelatedProduct\Block\Adminhtml\Rpr\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Rule\Model\Condition\AbstractCondition;

class DisplayItem extends Generic implements TabInterface
{
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
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        // $myfile = fopen("D:/xampp2/htdocs/magentov2/app/code/Magepow/AutoRelatedProduct/Controller/Adminhtml/Rpr/temp.txt", "a+");
        // $txt=$this->combine->getElementName();
        // fwrite($myfile, $txt);
        // fclose($myfile);
        $this->rendererFieldset = $rendererFieldset;
        $this->conditions = $conditions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Products to Display');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Products to Display');
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
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Products to Display')]);

        $fieldsetId = 'rule_conditions_fieldset'. uniqid();
        $formName = 'catalog_rule_form' . uniqid();

        $model = $this->_coreRegistry->registry('autorelatedproduct');

        $Display_Item_Parameters = $model->getData('display_item');
        $Display_Item_Parameters = @unserialize($Display_Item_Parameters);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $modelRule = $objectManager->get('Magento\CatalogWidget\Model\RuleFactory');
        $modelConditions = $modelRule->create();
        if (is_array($Display_Item_Parameters))
        {
            $modelConditions->loadPost($Display_Item_Parameters);
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
                    'Choose the conditions to define the products the "related items" block will be displayed for.'
                )
            ]
        )->setRenderer(
            $renderer
        );


        $fieldset->addField(
            'conditions_item',
            'text',
            ['name' => 'conditions_item', 'label' => __('conditions_item'), 'title' => __('conditions_item'),'data-form-parts' => $formName]
        )->setRule(
            $modelConditions
        )->setRenderer(
            $this->conditions
        );

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function toHtml()
    {
        $html = parent::toHtml();
        $html = str_replace("conditions","conditions_item",$html);
        return $html;
    }

}