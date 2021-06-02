<?php

namespace Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr;

class Edit extends \Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr
{
    /**
     * Rule edit action
     *
     * @return void
     */
    public function execute()
    {
        $have_error = NULL;
        $id = $this->getRequest()->getParam('id');
        /** @var \Vendor\Rules\Model\Rule $model */
        $model = $this->rprFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                $this->_redirect('magepow_autorelatedproduct/*');
                return;
            }else {
                $block_settings = explode(';',$model->getData('block_settings'));
                $model->setData('block_title',$block_settings[0]);
                $model->setData('sort_by',$block_settings[1]);
                $model->setData('product_limit',$block_settings[2]);
                $config = $this->json->unserialize($model->getData('config'));
                $breakpointsArr = $config['responsive'];
                $slide_optionArr = $config['config_options'];
                foreach ($this->responsive->getBreakpoints() as $key => $value) {
                    if($breakpointsArr[$value]!=NULL){
                        $model->setData($value,$breakpointsArr[$value]);
                    }
                }
                foreach ($model->slide_option as $option) {
                    if($slide_optionArr[$option]!=NULL){
                        $model->setData($option,$slide_optionArr[$option]);
                    }
                }
            }
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        $this->coreRegistry->register('autorelatedproduct', $model);

        $this->_initAction();
        $this->_view->getLayout()
            ->getBlock('rpr_edit')
            ->setData('action', $this->getUrl('magepow_autorelatedproduct/*/save'));

        $this->_addBreadcrumb($id ? __('Edit Rule') : __('New Rule'), $id ? __('Edit Rule') : __('New Rule'));

        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getRuleId() ? $model->getName() : __('New Rule')
        );
        $this->_view->renderLayout();
    } 
}