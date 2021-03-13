<?php

namespace Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr;

class Delete extends \Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr
{
    /**
     * Delete rule action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var \Vendor\Rules\Model\Rule $model */
                $model = $this->rprFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the rule.'));
                $this->_redirect('magepow_autorelatedproduct/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete the rule right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('magepow_autorelatedproduct/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a rule to delete.'));
        $this->_redirect('magepow_autorelatedproduct/*/');
    }
}