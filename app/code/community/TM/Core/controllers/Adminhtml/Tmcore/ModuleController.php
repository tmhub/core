<?php

class TM_Core_Adminhtml_Tmcore_ModuleController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/tmcore_module')
            ->_addBreadcrumb('Templates Master', 'Templates Master')
            ->_addBreadcrumb(Mage::helper('tmcore')->__('Modules'), Mage::helper('tmcore')->__('Modules'));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Placeholder grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function upgradeAction()
    {
        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('tmcore')->__('Upgrade'), Mage::helper('tmcore')->__('Upgrade'));

        $module = Mage::getModel('tmcore/module');
        $module->load($this->getRequest()->getParam('id'));
        Mage::register('tmcore_module', $module);

        $this->renderLayout();
    }

    public function skipAction()
    {
        //
    }

    public function upgradePostAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_redirect('*/*/index');
        }

        $stores = $this->getRequest()->getPost('stores', array());

        $module = Mage::getModel('tmcore/module');
        $module->load($this->getRequest()->getParam('id'));
        if (!$module->hasUpgradesToRun()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tmcore')->__("Module doesn't has upgrades to run"));
            $this->_redirect('*/*/');
            return;
        }
        $module->addStores($stores)->up();

        // clean cache, database cache
        // Mage::dispatchEvent('adminhtml_cache_flush_all');
        // Mage::app()->getCacheInstance()->flush();
        Mage::app()->cleanCache();
        Mage::dispatchEvent('adminhtml_cache_flush_system');

        // update indexes if attributes was added

        $this->_redirect('*/*/upgrade', array('id' => $module->getId()));
    }
}
