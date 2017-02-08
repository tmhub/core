<?php

class TM_Core_Block_Adminhtml_Renderer_Actions extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Returns array of html links, that will be wrapped into actions dropdown
     *
     * @param  Varien_Object $row [description]
     * @return array
     */
    public function getActions(Varien_Object $row)
    {
        return array();
    }

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $actions = $this->getActions($row);

        if (!$actions) {
            return '';
        }

        $result = '<div class="tm-action-select-wrap">';
        if (count($actions) > 1) {
            $result .= '<a href="javascript:void(0)" class="tm-action-select">'
                . Mage::helper('adminhtml')->__('Select')
                . '</a>';

            $result .= '<ul class="tm-action-menu">';
            foreach ($actions as $action) {
                $result .= '<li>' . $action . '</li>';
            }
            $result .= '</ul>';
        } else {
            foreach ($actions as $action) {
                $result .= $action;
            }
        }
        $result .= '</div>';

        return $result;
    }
}
