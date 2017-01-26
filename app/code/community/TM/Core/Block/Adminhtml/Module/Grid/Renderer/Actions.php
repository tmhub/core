<?php

class TM_Core_Block_Adminhtml_Module_Grid_Renderer_Actions
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
        $links = array();

        if ($row->getDocsLink()) {
            $links[] = sprintf(
                '<a href="%s" title="%s" onclick="window.open(this.href); return false;">%s</a>',
                $row->getDocsLink(),
                Mage::helper('tmcore')->__('View Docs'),
                Mage::helper('tmcore')->__('Docs')
            );
        }

        if ($row->getChangelogLink()) {
            $links[] = sprintf(
                '<a href="%s" title="%s" onclick="window.open(this.href); return false;">%s</a>',
                $row->getChangelogLink(),
                Mage::helper('tmcore')->__('View Changelog'),
                Mage::helper('tmcore')->__('Changelog')
            );
        }

        if ($row->getDownloadLink()) {
            $links[] = sprintf(
                '<a href="%s" title="%s" onclick="window.open(this.href); return false;">%s</a>',
                $row->getDownloadLink(),
                Mage::helper('tmcore')->__('Download Latest Version'),
                Mage::helper('tmcore')->__('Download')
            );
        }

        if ($row->hasUpgradesDir() || $row->getIdentityKeyLink()) {
            $links[] = sprintf(
                '<a href="%s">%s</a>',
                $this->getUrl('*/*/manage/', array('_current' => true, 'id' => $row->getId())),
                Mage::helper('tmcore')->__('Manage')
            );
        }

        return implode(' | ', $links);
    }
}
