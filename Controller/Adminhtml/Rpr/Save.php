<?php

namespace Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr;
class Save extends \Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr
{

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Vendor\Rules\Model\RuleFactory $ruleFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magepow\AutoRelatedProduct\Model\RprFactory $rprFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magepow\Core\Model\Config\Source\Responsive $responsive
    ) {
        parent::__construct($context, $coreRegistry, $fileFactory, $dateFilter, $rprFactory, $logger, $responsive);
    }

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
            $data['config']='';
            $count =0;
            $data['config']= '';
            foreach ($this->responsive->getBreakpoints() as $key => $value) {
                if($value == 'mobile') $count+=1;
                if($count == 0)$data['config'].=$data[$value].';';
                elseif($count == 1) $data['config'].=$data[$value].' || ';
            }
            foreach ($model->slide_option as $key) {
                $data['config'].=$data[$key];
                if($key!='review')$data['config'].=';';
            }
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
        $data['display_place'] = 'a:1:{s:10:"conditions";'.serialize($data['parameters']['conditions_display']).'}';
        $data['display_item'] = 'a:1:{s:10:"conditions";'.serialize($data['parameters']['conditions_item']).'}';
        return $data;
    }
}