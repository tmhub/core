<?php

class TM_Core_Model_Resource_Module_LocalCollection extends Varien_Data_Collection
{
    protected $_collectedModules = array();

    /**
     * Lauch data collecting
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return Varien_Data_Collection_Filesystem
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

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

        $this->_collectedModules = $modules;

        // calculate totals
        $this->_totalRecords = count($this->_collectedModules);
        $this->_setIsLoaded();

        // paginate and add items
        $from = ($this->getCurPage() - 1) * $this->getPageSize();
        $to = $from + $this->getPageSize() - 1;
        $isPaginated = $this->getPageSize() > 0;

        $cnt = 0;
        foreach ($this->_collectedModules as $row) {
            $cnt++;
            if ($isPaginated && ($cnt < $from || $cnt > $to)) {
                continue;
            }
            $item = new $this->_itemObjectClass();
            $this->addItem($item->addData($row));
            if (!$item->hasId()) {
                $item->setId($cnt);
            }
        }

        return $this;
    }
}
