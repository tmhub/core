<?php

class TM_Core_Model_Observer
{
    /**
     * Predispath admin action controller
     *
     * @param Varien_Event_Observer $observer
     */
    public function preDispatch(Varien_Event_Observer $observer)
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn()) {
            $feedModel = Mage::getModel('tmcore/notification_feed');
            $feedModel->checkUpdate();
        }
    }

    /**
     * Add layout update files just before local.xml
     * Conditions are supported too
     */
    public function addLayoutUpdate($observer)
    {
        // $area = Mage::getSingleton('core/design_package')->getArea();
        $area = Mage_Core_Model_App_Area::AREA_FRONTEND;
        $updates = $observer->getUpdates();
        $extraNodes = Mage::app()->getConfig()->getNode($area.'/tm_layout/updates');
        if (!$extraNodes) {
            return;
        }
        foreach ($extraNodes->children() as $node) {
            if ($node->getAttribute('condition')) {
                $parts  = explode('/', $node->getAttribute('condition'));
                $helper = array_shift($parts);
                $method = array_shift($parts);
                if (count($parts)) {
                    $helper .= '/' . $method;
                    $method = array_shift($parts);
                }
                $helper = Mage::helper($helper);
                if ($args = $node->getAttribute('args')) {
                    $args = explode(',', $args);
                    $enabled = call_user_func_array(array($helper, $method), $args);
                } else {
                    $enabled = $helper->{$method}();
                }
                if (!$enabled) {
                    continue;
                }
            }
            $updates->appendChild($node);
        }
    }
}
