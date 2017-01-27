<?php

class TM_Core_Block_Adminhtml_Module_Grid_Renderer_Actions extends TM_Core_Block_Adminhtml_Renderer_Actions
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function getActions(Varien_Object $row)
    {
        $links = array();

        if ($row->getVersionStatus() === TM_Core_Model_Module::VERSION_OUTDATED) {
            $links[] = sprintf(
                '<a href="%s">%s</a>',
                $this->getUrl('*/*/upgrade/', array('_current' => true, 'id' => $row->getId())),
                Mage::helper('tmcore')->__('Run Upgrades')
            );
        }

        if ($row->hasUpgradesDir() || $row->getIdentityKeyLink()) {
            $links[] = sprintf(
                '<a href="%s">%s</a>',
                $this->getUrl('*/*/manage/', array('_current' => true, 'id' => $row->getId())),
                Mage::helper('tmcore')->__('Open Installer')
            );
        }

        if ($row->getDocsLink()) {
            $links[] = sprintf(
                '<a href="%s" title="%s" onclick="window.open(this.href); return false;">%s</a>',
                $row->getDocsLink(),
                Mage::helper('tmcore')->__('Read Documentation'),
                Mage::helper('tmcore')->__('Read Documentation')
            );
        }

        if ($row->getChangelogLink()) {
            $links[] = sprintf(
                '<a href="%s" title="%s" onclick="window.open(this.href); return false;">%s</a>',
                $row->getChangelogLink(),
                Mage::helper('tmcore')->__('View Changelog'),
                Mage::helper('tmcore')->__('View Changelog')
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

        return $links;
    }
}
