<?php

class TM_Core_Block_Adminhtml_System_Config_Form_Field_Size
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setStyle('width:70px;')
            ->setName($element->getName() . '[]');

        if ($element->getValue()) {
            $values = explode(',', $element->getValue());
        } else {
            $values = array();
        }

        $width  = $element->setValue(isset($values[0]) ? $values[0] : null)->getElementHtml();
        $height = $element->setValue(isset($values[1]) ? $values[1] : null)->getElementHtml();
        return Mage::helper('sales')->__('Width') . ' ' . $width
            . ' '
            . Mage::helper('sales')->__('Height') . ' ' . $height;
    }
}
