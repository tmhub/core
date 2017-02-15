<?php

class TM_Core_Model_Adminhtml_System_Config_Backend_Serialized_Array
    extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{
    /**
     * Fixed inheritance for exception-like elements.
     * @see  app/code/core/Mage/Adminhtml/Model/Config/Data.php::getConfigDataValue:
     *
     *      This part of code:
     *      $data = $this->getConfigRoot()->descend($path);
     *
     *      Will return Mage_Core_Model_Config_Element instead of saved value
     *
     * @return string
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        if ($value instanceof Mage_Core_Model_Config_Element) {
            $this->setValue((string)$value);
        }
        parent::_afterLoad();
    }
}
