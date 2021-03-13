<?php

namespace Magepow\AutoRelatedProduct\Model\ResourceModel;

class Rpr extends \Magento\Rule\Model\ResourceModel\AbstractResource
{

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magepow_autorelatedproduct_rpr', 'rule_id');
    }
}