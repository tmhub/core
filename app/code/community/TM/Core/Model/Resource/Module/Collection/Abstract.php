<?php

class TM_Core_Model_Resource_Module_Collection_Abstract extends Varien_Data_Collection
{
    protected $_collectedModules = array();

    /**
     * Lauch data collecting
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return TM_Core_Model_Resource_Module_Collection_Abstract
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->_collectedModules = $this->_loadModules();

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

    protected function _loadModules()
    {
        return array();
    }
}
