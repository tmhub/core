<?php

class TM_Core_Block_Adminhtml_Subscription_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'tmcore';
        $this->_controller = 'adminhtml_subscription';

        parent::__construct();

        $this->setData('form_action_url', $this->getUrl('*/*/save'));
        $this->_updateButton('save', 'label', Mage::helper('cms')->__('Activate'));
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('tmcore')->__('Activate SwissUpLabs Subscription');
    }
}
