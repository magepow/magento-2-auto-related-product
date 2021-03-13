<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2020-06-03 17:50:51
 * @@Function:
 */

namespace Magepow\AutoRelatedProduct\Block\Adminhtml\Rpr\Edit\Tab;

class Config extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $_objectFactory;
    protected $_yesNo;
    protected $_trueFalse;
    protected $_row;

    /**
     * @var \Magiccart\Magicproduct\Model\Magicproduct
     */

    protected $_rpr;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magepow\AutoRelatedProduct\Model\Rpr $rpr,
        \Magepow\AutoRelatedProduct\Model\Options\Row $row,
        array $data = []
    ) {
        $this->_objectFactory = $objectFactory;
        $this->_yesNo = $yesNo;
        $this->_trueFalse = ['true' => __('True'), 'false' => __('False')];
        $this->_row = $row;
        $this->_rpr = $rpr;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('autorelatedproduct');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Config Tab Information')]);

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        $slidefieldset = $fieldset->addField('slide', 'select',
            [
                'label' => __('Slide'),
                'title' => __('Slide'),
                'name' => 'slide',
                'options' => $this->_yesNo->toArray(),
                'value' => 1,
            ]
        );

        // Option with value TRUE or FALSE
        $vertical = $fieldset->addField('vertical', 'select',
            [
                'label' => __('Slide Vertical'),
                'title' => __('Slide Vertical'),
                'name' => 'vertical',
                'options' => $this->_trueFalse,
                'value' => 'false',
            ]
        );

        $fieldset->addField('vertical-Swiping', 'select',
            [
                'label' => __('Vertical Swiping'),
                'title' => __('Vertical Swiping'),
                'name' => 'vertical-Swiping',
                'options' => $this->_trueFalse,
                'value' => 'false',
            ]
        );

        $vertical->setAfterElementHtml(
            '
                <script type="text/javascript">
                require([
                    "jquery",
                    "uiRegistry"
                ],  function($, uiRegistry){
                        jQuery(document).ready(function($) {
                            var verticalValue=$("#rule_vertical").children("option:selected").val();
                            switch (verticalValue){
                                case "false":
                                    $(".field-vertical-Swiping").hide();
                                    $(".field-fade").show();
                                    break;
                                case "true":
                                    $(".field-vertical-Swiping").show();
                                    $(".field-fade").hide();
                                    break;
                            }
                            $(".field-vertical").change(function ()
                            {
                                var verticalValue=$("#rule_vertical").children("option:selected").val();
                                switch (verticalValue){
                                    case "false":
                                        $(".field-vertical-Swiping").hide();
                                        $(".field-fade").show();
                                        break;
                                    case "true":
                                        $(".field-vertical-Swiping").show();
                                        $(".field-fade").hide();
                                        break;
                                }
                            });
                        })
                })
                </script>
            '
        );

        $fieldset->addField('infinite', 'select',
            [
                'label' => __('Infinite'),
                'title' => __('Infinite'),
                'name' => 'infinite',
                'options' => $this->_trueFalse,
            ]
        );

        $fieldset->addField('autoplay', 'select',
            [
                'label' => __('Auto Play'),
                'title' => __('Auto Play'),
                'name' => 'autoplay',
                'options' => $this->_trueFalse,
            ]
        );        

        $fieldset->addField('arrows', 'select',
            [
                'label' => __('Arrows'),
                'title' => __('Arrows'),
                'name' => 'arrows',
                'options' => $this->_trueFalse,
            ]
        );

        $fieldset->addField('dots', 'select',
            [
                'label' => __('Dots'),
                'title' => __('Dots'),
                'name' => 'dots',
                'options' => $this->_trueFalse,
                'value' => 'false',
            ]
        );

        $fieldset->addField('fade', 'select',
            [
                'label' => __('Fade'),
                'title' => __('fade'),
                'name' => 'fade',
                'options' => $this->_trueFalse,
                'value' => 'false',
            ]
        );

        $fieldset->addField('center-Mode', 'select',
            [
                'label' => __('Center Mode'),
                'title' => __('Center Mode'),
                'name' => 'center-Mode',
                'options' => $this->_trueFalse,
                'value' => 'false',
            ]
        );

        $fieldset->addField('rows', 'select',
            [
                'label' => __('Rows'),
                'title' => __('Rows'),
                'name' => 'rows',
                'options' => $this->_row->toOptionArray(),
                'value' => '1',
            ]
        );

        // End option with value TRUE or FALSE

        // Option Text
        $fieldset->addField('speed', 'text',
            [
                'label' => __('Speed'),
                'title' => __('Speed'),
                'name'  => 'speed',
                'required' => true,
                'class' => 'validate-zero-or-greater',
                'value' => 300,
            ]
        );

        $fieldset->addField('autoplay-Speed', 'text',
            [
                'label' => __('autoplay Speed'),
                'title' => __('autoplay Speed'),
                'name'  => 'autoplay-Speed',
                'required' => true,
                'class' => 'validate-zero-or-greater',
                'value' => 3000,
            ]
        );

        $fieldset->addField('padding', 'text',
            [
                'label' => __('Padding'),
                'title' => __('Padding'),
                'name'  => 'padding',
                'required' => true,
                'class' => 'validate-zero-or-greater',
                'value' => 15,
            ]
        );

        $fieldset->addField('widthImages', 'text',
            [
                'label' => __('width Images'),
                'title' => __('width Images'),
                'name'  => 'widthImages',
                'required' => true,
                'class' => 'validate-greater-than-zero',
                'value' => 300,
            ]
        );

        $fieldset->addField('heightImages', 'text',
            [
                'label' => __('height Images'),
                'title' => __('height Images'),
                'name'  => 'heightImages',
                'required' => true,
                'class' => 'validate-greater-than-zero',
                'value' => 300,
            ]
        );

        // End option Text

        $fieldset->addField(
            'cart',
            'select',
            [
                'label' => __('Show Cart'),
                'title' => __('Show Cart'),
                'name' => 'cart',
                'options' => $this->_yesNo->toArray(),
                'value' => 1,
            ]
        );

        $fieldset->addField(
            'compare',
            'select',
            [
                'label' => __('Show Compare'),
                'title' => __('Show Compare'),
                'name' => 'compare',
                'options' => $this->_yesNo->toArray(),
                'value' => 1,
            ]
        );

        $fieldset->addField(
            'wishlist',
            'select',
            [
                'label' => __('Show Wishlist'),
                'title' => __('Show Wishlist'),
                'name' => 'wishlist',
                'options' => $this->_yesNo->toArray(),
                'value' => 1,
            ]
        );

        $fieldset->addField(
            'review',
            'select',
            [
                'label' => __('Show Review'),
                'title' => __('Show Review'),
                'name' => 'review',
                'options' => $this->_yesNo->toArray(),
                'value' => 1,
            ]
        );

        $slidefieldset->setAfterElementHtml(
            '
                <script type="text/javascript">
                require([
                    "jquery",
                    "uiRegistry"
                ],  function($, uiRegistry){
                        jQuery(document).ready(function($) {
                            var slidevalue=$("#rule_slide").children("option:selected").val();
                            switch (slidevalue){
                                case "0":
                                    $(".field-vertical").hide();
                                    $(".field-vertical-Swiping").hide();
                                    $(".field-infinite").hide();
                                    $(".field-autoplay").hide();
                                    $(".field-arrows").hide();
                                    $(".field-dots").hide();
                                    $(".field-rows").hide();
                                    $(".field-speed").hide();
                                    $(".field-autoplay-Speed").hide();
                                    $(".field-fade").hide();
                                    $(".field-center-Mode").hide();
                                    break;
                                case "1":
                                    $(".field-vertical").show();
                                    $(".field-vertical-Swiping").show();
                                    $(".field-infinite").show();
                                    $(".field-autoplay").show();
                                    $(".field-arrows").show();
                                    $(".field-dots").show();
                                    $(".field-rows").show();
                                    $(".field-speed").show();
                                    $(".field-autoplay-Speed").show();
                                    $(".field-fade").show();
                                    $(".field-center-Mode").show();
                                    break;
                            }
                            $(".field-slide").change(function ()
                            {
                                var slidevalue=$("#rule_slide").children("option:selected").val();
                                switch (slidevalue){
                                    case "0":
                                        $(".field-vertical").hide();
                                        $(".field-vertical-Swiping").hide();
                                        $(".field-infinite").hide();
                                        $(".field-autoplay").hide();
                                        $(".field-arrows").hide();
                                        $(".field-dots").hide();
                                        $(".field-rows").hide();
                                        $(".field-speed").hide();
                                        $(".field-autoplay-Speed").hide();
                                        $(".field-fade").hide();
                                        $(".field-center-Mode").hide();
                                        break;
                                    case "1":
                                        $(".field-vertical").show();
                                        $(".field-vertical-Swiping").show();
                                        $(".field-infinite").show();
                                        $(".field-autoplay").show();
                                        $(".field-arrows").show();
                                        $(".field-dots").show();
                                        $(".field-rows").show();
                                        $(".field-speed").show();
                                        $(".field-autoplay-Speed").show();
                                        $(".field-fade").show();
                                        $(".field-center-Mode").show();
                                        break;
                                }
                            });
                        })
                })
                </script>
            '
        );

        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Config Information');
    }

    /**
     * Prepare title for tab.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
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
}
