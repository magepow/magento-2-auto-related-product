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
                $tmp = explode(';',$model->getData('block_settings'));
                $model->setData('block_title',$tmp[0]);
                $model->setData('sort_by',$tmp[1]);
                $model->setData('product_limit',$tmp[2]);
                $tmp2 = explode(' || ', $model->getData('config'));
                $tmp2_1 = explode(';',$tmp2[0]);
                $tmp2_2 = explode(';',$tmp2[1]);
                $count_responsive =0;
                foreach ($this->responsive->getBreakpoints() as $key => $value) {
                    if($key!=1){
                        $model->setData($value,$tmp2_1[$count_responsive]);
                        $count_responsive+=1;
                    }
                }
                $count_config =0 ;
                foreach ($model->slide_option as $key) {
                    $model->setData($key,$tmp2_2[$count_config]);
                    $count_config+=1;
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