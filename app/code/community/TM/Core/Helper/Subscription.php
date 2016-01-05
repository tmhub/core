<?php

class TM_Core_Helper_Subscription extends Mage_Core_Helper_Abstract
{
    public function canValidateConfigSection($section)
    {
        $sections = Mage::getSingleton('adminhtml/config')->getSections($section);
        $tab = (string)$sections->$section->tab;

        $tabsToValidate = array(
            'templates_master',
            'tm_checkout'
        );
        if (!in_array($tab, $tabsToValidate)) {
            return false;
        }

        $ignoredSections = array(); // @todo get from config.xml
        if (in_array($section, $ignoredSections)) {
            return false;
        }
        return true;
    }

    public function validateSubscription()
    {
        $module = Mage::getModel('tmcore/module');
        $module->load('Swissup_Subscription');

        $result = array();
        if (!$module->getIdentityKey()) {
            $url = Mage::helper('adminhtml')->getUrl('*/tmcore_subscription/index');
            $result['error'] = Mage::helper('tmcore')->__(
                'Please %s SwissUpLabs subscription to use this module',
                sprintf(
                    "<a href='{$url}'>%s</a>",
                    Mage::helper('tmcore')->__('activate')
                )
            );
        } else {
            $result = $module->validateLicense();
            if (is_array($result) && isset($result['error'])) {
                // try to translate remote response
                $result['error'] = call_user_func_array(array(Mage::helper('tmcore'), '__'), $result['error']);
            }
        }

        return $result;
    }
}
