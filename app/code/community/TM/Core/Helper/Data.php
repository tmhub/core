<?php

class TM_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isDesignPackageEquals($packageName)
    {
        $package = Mage::getSingleton('core/design_package');
        return $package->getPackageName() === $packageName;
    }
}
