<?php

class TM_Core_Block_Adminhtml_Module_Upgrade extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'tmcore';
        $this->_controller = 'adminhtml_module';
        $this->_mode       = 'upgrade';

        parent::__construct();

        $this->setData('form_action_url', $this->getUrl('*/*/upgradePost'));
        $this->_updateButton('save', 'label', Mage::helper('tmcore')->__('Run'));
        $this->_removeButton('delete');
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $model = Mage::registry('tmcore_module');
        if ($model->getDataVersion()) { // module is installed already
            if ($model->getUpgradesToRun()) {
                $label = 'Upgrade and Install/Reinstall %s %s (Data version %s)';
            } else {
                $label = 'Install or Reinstall %s %s (Data version %s)';
            }
            return Mage::helper('tmcore')->__(
                $label,
                $model->getCode(),
                $model->getVersion(),
                $model->getDataVersion()
            );
        }
        return Mage::helper('tmcore')->__(
            'Install %s %s',
            $model->getCode(),
            $model->getVersion()
        );
    }

    public function getSkipUrl()
    {
        return $this->getUrl('*/*/skip', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }
}
