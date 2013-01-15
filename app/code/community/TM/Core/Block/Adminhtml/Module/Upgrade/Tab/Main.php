<?php

class TM_Core_Block_Adminhtml_Module_Upgrade_Tab_Main
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $model = Mage::registry('tmcore_module');

        $form = new Varien_Data_Form(
            array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post')
        );

        $form->setHtmlIdPrefix('module_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('cms')->__('General Information'),
            'class'  => 'fieldset-wide'
        ));

        $fieldset->addField('code', 'hidden', array(
            'name' => 'id'
        ));

        $field = $fieldset->addField('store_id', 'multiselect', array(
            'name'      => 'stores[]',
            'label'     => Mage::helper('cms')->__('Stores to install and activate module'),
            'title'     => Mage::helper('cms')->__('Stores to install and activate module'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
        ));
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);

//        if ($operations = $model->getUpgradeOperationsAsString($model->getDataVersion())) {
//            $field = $fieldset->addField('upgrade_operation', 'textarea', array(
//                'name'     => 'todo',
//                'label'    => Mage::helper('cms')->__('What will be done'),
//                'title'    => Mage::helper('cms')->__('What will be done'),
//                'value'    => $operations,
//                'readonly' => 1,
//                'style'    => 'height: 300px;'
//            ));
//        }

        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('cms')->__('Main');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('cms')->__('Main');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('tmcore/module/' . $action);
    }
}
