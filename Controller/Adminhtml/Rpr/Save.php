<?php

namespace Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr;
class Save extends \Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr
{
    /**
     * Rule save action
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->getPostValue()) {
            $this->_redirect('magepow_autorelatedproduct/*/');
        }
        
        try {
            /** @var $model \Vendor\Rules\Model\Rule */
            $model = $this->rprFactory->create();
            $this->_eventManager->dispatch(
                'adminhtml_controller_magepow_autorelatedproduct_prepare_save',
                ['request' => $this->getRequest()]
            );
            $data = $this->getRequest()->getPostValue();

            $id = $this->getRequest()->getParam('rule_id');
            if ($id) {
                $model->load($id);
            }else {
                $check = $model->getCollection()
                            ->addFieldToFilter('name', $data['name']);
                if($check->count()){
                    $this->messageManager->addError(__('Rule\'s Name already exists.'));
                    $this->_session->setPageData($data);
                    $this->_redirect('magepow_autorelatedproduct/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
                    return;
                }
            }

            $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
                $this->_session->setPageData($data);
                $this->_redirect('magepow_autorelatedproduct/*/edit', ['id' => $model->getId()]);
                return;
            }
            $data['store_id']=implode(',',$data['store_id']);
            $data['customer_group_id']=implode(',',$data['customer_group_id']);
            $data['block_settings']=$data['block_title'].';'.$data['sort_by'].';'.$data['product_limit'];
            $configArr = [];
            $breakpointsArr= [];
            foreach ($this->responsive->getBreakpoints() as $key => $value) {
                $breakpointsArr[$value]= $data[$value];
            }
            $configArr['responsive']= $breakpointsArr;
            $slide_optionArr = [];
            foreach ($model->slide_option as $option) {
                $slide_optionArr[$option]= $data[$option];
            }
            $configArr['config_options']= $slide_optionArr;
            $data['config']=$this->json->serialize($configArr);
            if(array_key_exists('display_to_category',$data))$data['display_to_category']=implode(',',$data['display_to_category']);
            $data = $this->prepareData($data);
            $model->loadPost($data);

            $this->_session->setPageData($model->getData());

            $model->save();
            $this->messageManager->addSuccessMessage(__('You saved the rule.'));
            $this->_session->setPageData(false);
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('magepow_autorelatedproduct/*/edit', ['id' => $model->getId()]);
                return;
            }
            $this->_redirect('magepow_autorelatedproduct/*/');
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $id = (int)$this->getRequest()->getParam('rule_id');
            if (!empty($id)) {
                $this->_redirect('magepow_autorelatedproduct/*/edit', ['id' => $id]);
            } else {
                $this->_redirect('magepow_autorelatedproduct/*/new');
            }
            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the rule data. Please review the error log.')
            );
            $this->logger->critical($e);
            $data = !empty($data) ? $data : [];
            $this->_session->setPageData($data);
            $this->_redirect('magepow_autorelatedproduct/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
            return;
        }
    }

    /**
     * Prepares specific data
     *
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {
        $data['display_place'] = ($data['parameters']['conditions_display']!=NULL)? $this->json->serialize($data['parameters']['conditions_display']) : NULL;
        $data['display_item'] = ($data['parameters']['conditions_item']!=NULL)? $this->json->serialize($data['parameters']['conditions_item']) : NULL;
        return $data;
    }
}