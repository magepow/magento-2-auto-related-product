<?php

namespace Magepow\AutoRelatedProduct\Block\Adminhtml\Rpr\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rprs_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Related Product Rule'));
    }
}