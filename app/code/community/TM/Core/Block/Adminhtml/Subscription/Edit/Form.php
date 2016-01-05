<?php

class TM_Core_Block_Adminhtml_Subscription_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('subscription_form');
        $this->setTitle(Mage::helper('tmcore')->__('Subscription Key'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('tmcore_module');

        $form = new Varien_Data_Form(
            array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post')
        );

        $form->setHtmlIdPrefix('subscription_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            array('legend'=>Mage::helper('cms')->__('General Information'), 'class' => 'fieldset-wide')
        );

        $note = '';
        if ($model->getRemote()) {
            $link = $model->getRemote()->getIdentityKeyLink();
            $note = Mage::helper('tmcore')->__(
                'Get your identity key at <a href="%s" title="%s" target="_blank">%s</a>',
                $link,
                $link,
                $link
            );
        }
        $fieldset->addField('identity_key', 'textarea', array(
            'name'  => 'identity_key',
            'required' => true,
            'label' => Mage::helper('tmcore')->__('Identity Key'),
            'title' => Mage::helper('tmcore')->__('Identity Key'),
            'note'  => $note
        ));

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
