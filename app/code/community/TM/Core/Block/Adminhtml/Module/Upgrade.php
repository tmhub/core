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

        $mode = $this->getRequest()->getParam('upgrade_mode');
        // if ('upgrade' === $mode) {
//            $this->_addButton('skip', array(
//                'label'   => Mage::helper('adminhtml')->__('Skip this upgrade'),
//                'onclick' => 'if (confirm(\'' . Mage::helper('cms')->__('Are you sure want to mark module data as updated?') . '\')) { setLocation(\'' . $this->getSkipUrl() . '\'); }',
//                'class'   => 'delete'
//            ));
        // }
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $model = Mage::registry('tmcore_module');
        if ($model->isInstalled()) {
            return Mage::helper('tmcore')->__('Upgrade %s data to %s', $model->getCode(), $model->getDataVersion());
        } else {
            return Mage::helper('tmcore')->__('Install %s %s', $model->getCode(), $model->getVersion());
        }
    }

    public function getSkipUrl()
    {
        return $this->getUrl('*/*/skip', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }
}
