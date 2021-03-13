<?php

namespace Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr;

class Index extends \Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Related Product Rule'), __('Related Product Rule'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Related Product Rule'));
        $this->_view->renderLayout('root');
    }
}