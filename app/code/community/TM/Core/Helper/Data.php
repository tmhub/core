<?php

class TM_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isDesignPackageEquals($packageName)
    {
        if ($this->_getRequest()->getControllerName() == 'widget_instance'
            && $this->_getRequest()->getActionName() == 'blocks') {
            //fix for layout updates in widgets interface in magento admin
            return true;
        };
        $package = Mage::getSingleton('core/design_package');
        return $package->getPackageName() === $packageName;
    }
}
