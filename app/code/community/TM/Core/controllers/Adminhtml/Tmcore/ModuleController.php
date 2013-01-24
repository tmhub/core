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

        /**
         * @var TM_Core_Model_Module
         */
        $module = Mage::getModel('tmcore/module');
        $module->load($this->getRequest()->getParam('id'));
        if (!$module->hasUpgradesDir()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tmcore')->__("Module upgrade files are not found"));
            $this->_redirect('*/*/');
            return;
        }

        $module->setSkipUpgrade($this->getRequest()->getPost('skip_upgrade', false))
            ->setNewStores($this->getRequest()->getPost('stores', array()))
            ->setIdentityKey($this->getRequest()->getParam('identity_key'))
            ->up();
        Mage::app()->cleanCache();
        Mage::dispatchEvent('adminhtml_cache_flush_system');

        $groupedErrors = $module->getMessageLogger()->getErrors();
        if (count($groupedErrors)) {
            foreach ($groupedErrors as $type => $errors) {
                foreach ($errors as $error) {
                    if (is_array($error)) {
                        $message = $error['message'];
                    } else {
                        $message = $error;
                    }
                    Mage::getSingleton('adminhtml/session')->addError($message);
                }
            }
            $this->_redirect('*/*/upgrade', array('id' => $module->getId()));
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('tmcore')->__("The module has been installed"));
            $this->_redirect('*/*/');
        }
    }
}
