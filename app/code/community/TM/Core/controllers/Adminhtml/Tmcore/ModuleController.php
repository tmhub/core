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

    public function manageAction()
    {
        if (!$this->getRequest()->getParam('id')) {
            return $this->_redirect('*/*/index');
        }

        $module = Mage::getModel('tmcore/module');
        $module->load($this->getRequest()->getParam('id'));

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $module->addData($data);
        }

        if ($info = Mage::getSingleton('adminhtml/session')->getTmValidationInfo(true)) {
            $link = Mage::helper('tmcore/debug')->preparePopup(
                $info['response'],
                'SwissUpLabs subscription validation response'
            );
            Mage::getSingleton('adminhtml/session')->addError(
                $info['error'] . ' | ' . $link
            );
        }

        Mage::register('tmcore_module', $module);

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('tmcore')->__('Manage'), Mage::helper('tmcore')->__('Manage'));
        $this->renderLayout();
    }

    public function runAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->_redirect('*/*/index');
        }

        /**
         * @var TM_Core_Model_Module
         */
        $module = Mage::getModel('tmcore/module');
        $module->load($this->getRequest()->getParam('id'))
            ->setSkipUpgrade($this->getRequest()->getPost('skip_upgrade', false))
            ->setNewStores($this->getRequest()->getPost('new_stores', array()))
            ->setIdentityKey($this->getRequest()->getParam('identity_key'));

        $result = $module->validateLicense();
        if (is_array($result) && isset($result['error'])) {
            Mage::getSingleton('adminhtml/session')->setFormData($this->getRequest()->getPost());

            $error = call_user_func_array(array(Mage::helper('tmcore'), '__'), $result['error']);
            if (isset($result['response'])) {
                Mage::getSingleton('adminhtml/session')->setTmValidationInfo(
                    array(
                        'error'    => $error,
                        'response' => $result['response']
                    )
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addError($error);
            }
            return $this->_redirect('*/*/manage', array('id' => $module->getId()));
        }

        $module->up();

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
            Mage::getSingleton('adminhtml/session')->setFormData($this->getRequest()->getPost());
            return $this->_redirect('*/*/manage', array('id' => $module->getId()));
        }

        Mage::getSingleton('adminhtml/session')->setFormData(false);
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('tmcore')->__("The module has been saved"));
        $this->_redirect('*/*/');
    }

    /**
     * Run module upgrades
     */
    public function upgradeAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            $this->_redirect('*/*/');
        }

        /**
         * @var TM_Core_Model_Module
         */
        $module = Mage::getModel('tmcore/module');
        $module->load($id);
        $module->up();

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
            return $this->_redirect('*/*/', array('id' => $module->getId()));
        }

        Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('tmcore')->__("Module upgrades successfully applied")
        );
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcore_module');
    }
}
