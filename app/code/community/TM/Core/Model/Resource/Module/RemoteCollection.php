<?php

class TM_Core_Model_Resource_Module_RemoteCollection extends Varien_Data_Collection
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
        if ($this->isLoaded()) { // @todo
            return $this;
        }

        // data received from https://templates-master.com/catalog/feed/
        // @todo get data from feed
        $modules = array(
            'TM_Core' => array(
                'code'          => 'TM_Core',
                'version'       => '1.0.1',
                'changelog'     => '',
                'link'          => '',
                'download_link' => '',
                'identity_key_link'  => ''
            ),
            'TM_License' => array(
                'code'          => 'TM_License',
                'version'       => '1.0.0',
                'changelog'     => '',
                'link'          => '',
                'download_link' => '',
                'identity_key_link'  => ''
            ),
            'TM_Argento' => array(
                'code'          => 'TM_Argento',
                'version'       => '1.0.0',
                'changelog'     => '',
                'link'          => '',
                'download_link' => '',
                'identity_key_link'  => ''
            ),
            'TM_ArgentoArgento' => array(
                'code'          => 'TM_ArgentoArgento',
                'version'       => '1.0.0',
                'changelog'     => "",
                'link'          => 'http://argentotheme.com',
                'download_link' => 'https://argentotheme.com/downloadable/customer/products/',
                'identity_key_link'  => 'https://argentotheme.com/license/customer/identity/'
            )
        );
        foreach ($modules as $moduleName => $values) {
            $values['id'] = $values['code'];
            $this->_collectedModules[$values['code']] = $values;
        }

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
