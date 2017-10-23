<?php

class TM_Core_Model_Resource_Module_LocalCollection extends TM_Core_Model_Resource_Module_Collection_Abstract
{
    /**
     * Lauch data collecting
     *
     * @return array
     */
    protected function _loadModules()
    {
        $modules = array();
        $nodes = Mage::getConfig()->getNode('modules')->children();
        foreach ($nodes as $code => $info) {
            if (strpos($code, 'TM_') !== 0) {
                continue;
            }
            $isActive = (string)$info->active;
            if ($info->tm_hidden || in_array($isActive, array('false', '0'))) {
                continue;
            }

            $modules[$code] = $info->asArray();
            $modules[$code]['code'] = $code;
            $modules[$code]['id'] = $code;

            if (isset($modules[$code]['depends']) && is_array($modules[$code]['depends'])) {
                $modules[$code]['depends'] = array_keys($modules[$code]['depends']);
            } else {
                $modules[$code]['depends'] = array();
            }
        }

        return $modules;
    }
}
