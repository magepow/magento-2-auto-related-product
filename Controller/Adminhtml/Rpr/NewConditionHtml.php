<?php

namespace Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr;

class NewConditionHtml extends \Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr
{
    /**
     * New condition html action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];
        $form = $this->getRequest()->getParam('form');
        $model = $this->_objectManager->create(
            $type
        )->setId(
            $id
        )->setType(
            $type
        )->setRule(
            $this->rprFactory->create()
        )->setPrefix(
            'conditions'
        );
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            if (strpos($form, 'conditions_display') !== false) {
                $model->setPrefix('conditions_display');
                $model->setConditions([]);
            }
            if (strpos($form, 'conditions_item') !== false) {
                $model->setPrefix('conditions_item');
                $model->setConditions([]);
            }
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}