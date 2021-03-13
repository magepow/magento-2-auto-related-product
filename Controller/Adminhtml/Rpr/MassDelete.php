<?php
namespace Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr;

use Magepow\AutoRelatedProduct\Model\ResourceModel\Rpr\CollectionFactory;

class MassDelete extends \Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr
{
    public function execute()
    {
        $ruleIds = $this->getRequest()->getParam('delete', array());
        // var_dump($ruleIds);
        // die();
        $model = $model = $this->rprFactory->create();
        // $resModel = $this->_resPostsFactory->create();
        if(count($ruleIds))
        {
            $i = 0;
            foreach ($ruleIds as $ruleId) {
                try {
                    $model->load($ruleId);
                    $model->delete();
                    $i++;
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
            if ($i > 0) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 item(s) were deleted.', $i)
                );
            }
        }
        else
        {
            $this->messageManager->addErrorMessage(
                __('You can not delete item(s), Please check again %1')
            );
        }
        $this->_redirect('*/*/index');
    }
}