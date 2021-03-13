<?php

namespace Magepow\AutoRelatedProduct\Block\Adminhtml;

class Rpr extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'rpr';
        $this->_headerText = __('Related Product Rule');
        $this->_addButtonLabel = __('Add New Rule');
        parent::_construct();
    }
}