<?php

class TM_Core_Block_Adminhtml_Module_Grid_Filter_Version
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    const VERSION_AVAILABLE     = 'available';
    const VERSION_UNAVAILABLE   = 'unavailable';

    protected static $_options = array(
        null                        =>  null,
        self::VERSION_AVAILABLE     => 'Available',
        self::VERSION_UNAVAILABLE   => 'Unavailable',
    );

    protected function _getOptions()
    {
        $result = array();
        foreach (self::$_options as $code => $label) {
            $result[] = array(
                'value' => $code,
                'label' => Mage::helper('tmcore')->__($label)
            );
        }

        return $result;
    }

    public function getCondition()
    {
        switch ($this->getValue()) {
            case self::VERSION_AVAILABLE:
                return array('neq' => '');
            case self::VERSION_UNAVAILABLE:
                return array('eq' => '');
            default:
                return null;
        }
    }
}
