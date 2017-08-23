<?php

class TM_Core_Model_Observer_Module
{
    protected function _getRemoteCollection()
    {
        return Mage::getResourceModel('tmcore/module_remoteCollection');
    }

    protected function _getLocalCollection()
    {
        return Mage::getResourceModel('tmcore/module_localCollection');
    }

    /**
     * Load module data from remote server
     * @param  [type] $observer [description]
     * @return [type]           [description]
     */
    public function loadRemoteData($observer)
    {
        foreach ($this->_getRemoteCollection() as $remote) {
            $module = Mage::getModel('tmcore/module')
                ->load($remote->getCode())
                ->addData($remote->getData())
                ->save();
        }
    }

    /**
     * Load local module data. Used for modules that are not available remotely.
     * @param  [type] $observer [description]
     * @return [type]           [description]
     */
    public function loadLocalData($observer)
    {
        foreach ($this->_getRemoteCollection() as $remote) {
            $module = Mage::getModel('tmcore/module')
                ->load($remote->getCode())
                ->addData($remote->getData())
                ->save();
        }
    }

    /**
     * 1. Add remote links to the collection
     * 2. Add local data to the collection (codePool)
     *
     * @param  [type] $observer [description]
     * @return [type]           [description]
     */
    public function addModuleData($observer)
    {
        $remoteCollection = $this->_getRemoteCollection();
        $localCollection  = $this->_getLocalCollection();
        foreach ($observer->getModuleCollection() as $module) {
            $remote = $remoteCollection->getItemById($module->getCode());
            if ($remote) {
                $module->addData($remote->getData());
            }

            $local = $localCollection->getItemById($module->getCode());
            if ($local) {
                $module->addData($local->getData());
            }
        }
    }
}
