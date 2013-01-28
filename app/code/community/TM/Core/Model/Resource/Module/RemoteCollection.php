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

        // data received from https://templates-master.com/modules
        $modules = array(
            'TM_Core' => array(
                'code'          => 'TM_Core',
                'version'       => '1.0.1',
                'changelog'     => "1.0.1
Modules page added with latest module version
Automatic installer added

1.0.0
Configuration section is now available
List of installed modules
Contact link
Notifier added
",
                'link'          => 'http://templates-master.com/magento-ajaxpro.html',
                'download_link' => 'https://templates-master.com/downloadable/customer/products/',
                'identity_key_link'  => 'https://templates-master.com/license/customer/identity/'
            ),
            'TM_AjaxPro' => array(
                'code'          => 'TM_AjaxPro',
                'version'       => '2.1.0',
                'changelog'     => "2.1.0
Plain text.
No html is allowed

2.1.0
Plain text.
No html is allowed

2.1.0
Plain text.
No html is allowed

2.1.0
Plain text.
No html is allowed

2.1.0
Plain text.
No html is allowed

1.2.0
Feature
Feature
Feature
Bugfix

1.0.0
Release",
                'link'          => 'http://templates-master.com/magento-ajaxpro.html',
                'download_link' => 'https://templates-master.com/downloadable/customer/products/',
                'identity_key_link'  => ''
            ),
            'TM_AjaxSearch' => array(
                'code'          => 'TM_AjaxSearch',
                'version'       => '1.4.0',
                'changelog'     => "2.1.0",
                'link'          => 'http://templates-master.com/magento-ajax-search.html',
                'download_link' => 'https://templates-master.com/downloadable/customer/products/'
            ),
            'TM_Akismet' => array(
                'code'          => 'TM_Akismet',
                'version'       => '1.0.0',
                'changelog'     => "2.1.0"
            ),
            'TM_ArgentoArgento' => array(
                'code'          => 'TM_ArgentoArgento',
                'version'       => '1.0.0',
                'changelog'     => "2.1.0
Hello world.
",
                'data_version'  => '1.0.0',
                'link'          => 'http://templates-master.com/magento-templates/mobile-star-android-and-iphone-theme-for-magento.html',
                'download_link' => 'https://templates-master.com/downloadable/customer/products/'
            ),
            'TM_EasyBanner ' => array(
                'code'      => 'TM_EasyBanner',
                'version'   => '1.2.4',
                'changelog' => "1.2.4
Validate xml identifier class removed from placeholder's parent block, to allow to make reference to any block
1.2.3
Added support of widgets and blocks in banner html content
1.2.2
Fixed backend banner pagination
Fixed backend banner filter by clicks and display counts"
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
