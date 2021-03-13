<?php

namespace Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr;

class NewAction extends \Magepow\AutoRelatedProduct\Controller\Adminhtml\Rpr
{
    /**
     * New action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}