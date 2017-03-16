<?php

class TM_Core_Block_Adminhtml_Module_Grid_Renderer_Version
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $currentVersion = $row->getData($this->getColumn()->getIndex());

        if (!$currentVersion) {
            return 'N/A';
        }

        $latestVersion = $row->getData('latest_version');

        $result = version_compare($currentVersion, $latestVersion, '>=');
        if ($result) {
            $severity = 'grid-severity-notice';
            $title = $this->__('Module is up to date');
        } else {
            $severity = 'grid-severity-major';
            $title = $this->__("The latest version is %s", $latestVersion);
        }

        return '<span class="' . $severity . '" title="' . $title . '"><span>'
            . $currentVersion
            . '</span></span>';
    }
}
