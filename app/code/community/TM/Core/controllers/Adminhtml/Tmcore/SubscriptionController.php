<?php

class TM_Core_Adminhtml_Tmcore_SubscriptionController extends Mage_Adminhtml_Controller_Action
{
    const MODULE_CODE = 'Swissup_Subscription';

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/tmcore_subscription')
            ->_addBreadcrumb('Templates Master', 'Templates Master')
            ->_addBreadcrumb(
                Mage::helper('tmcore')->__('Activate SwissUpLabs Subscription'),
                Mage::helper('tmcore')->__('Activate SwissUpLabs Subscription')
            );
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();

        $module = Mage::getModel('tmcore/module');
        $module->load(self::MODULE_CODE);

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $module->addData($data);
        }

        Mage::register('tmcore_module', $module);

        $this->renderLayout();
    }

    /**
     * Copy from ModuleController::Run action
     */
    public function saveAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->_redirect('*/*/index');
        }

        /**
         * @var TM_Core_Model_Module
         */
        $module = Mage::getModel('tmcore/module');
        $module->load(self::MODULE_CODE)
            ->setNewStores(array(0))
            ->setIdentityKey($this->getRequest()->getParam('identity_key'));

        $result = $module->validateLicense();
        if (is_array($result) && isset($result['error'])) {
            Mage::getSingleton('adminhtml/session')->setFormData($this->getRequest()->getPost());
            Mage::getSingleton('adminhtml/session')->addError(
                // try to translate remote response
                call_user_func_array(array(Mage::helper('tmcore'), '__'), $result['error'])
            );
            return $this->_redirect('*/*/index');
        }

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
            Mage::getSingleton('adminhtml/session')->setFormData($this->getRequest()->getPost());
            return $this->_redirect('*/*/index');
        }

        Mage::getSingleton('adminhtml/session')->setFormData(false);
        Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('tmcore')->__("Subscription has been activated")
        );
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcore_subscription');
    }
}
