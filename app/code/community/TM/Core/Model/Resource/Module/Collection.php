<?php

class TM_Core_Model_Resource_Module_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_eventPrefix = 'tm_core_module_collection';

    protected $_eventObject = 'module_collection';

    protected $_idFieldName = 'code';

    protected function _construct()
    {
        $this->_init('tmcore/module');
    }
}
