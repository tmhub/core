<?php

class TM_Core_Model_Module extends Mage_Core_Model_Abstract
{
    const VERSION_UPDATED    = 1;
    const VERSION_OUTDATED   = 2; // new upgrades are avaialble
    const VERSION_DEPRECATED = 3; // new version is avaialble but now uploaded

    /**
     * @var TM_Core_Model_Module_ErrorLogger
     */
    protected static $_messageLogger = null;

    protected function _construct()
    {
        $this->_init('tmcore/module');
    }

    /**
     * Check for store_ids type added
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $stores = $this->getStoreIds();
        if (is_array($stores)) {
            $this->setStoreIds(implode(',', array_unique($stores)));
        }
        return parent::_beforeSave();
    }

    public function load($id, $field=null)
    {
        parent::load();

        $xml = Mage::getConfig()->getNode('modules/' . $id);
        $this->setId($id);
        $this->setDepends(array());
        if ($xml) {
            $data = $xml->asCanonicalArray();
            if (isset($data['depends']) && is_array($data['depends'])) {
                $data['depends'] = array_keys($data['depends']);
            } else {
                $data['depends'] = array();
            }
            $this->addData($data);
        }

        return $this;
    }

    /**
     * Retrieve Severity collection array
     *
     * @return array|string
     */
    public function getVersionStatuses($status = null)
    {
        $versionStatuses = array(
            self::VERSION_UPDATED    => Mage::helper('tmcore')->__('updated'),
            self::VERSION_OUTDATED   => Mage::helper('tmcore')->__('outdated'),
            self::VERSION_DEPRECATED => Mage::helper('tmcore')->__('deprecated')
        );

        if (!is_null($status)) {
            if (isset($versionStatuses[$status])) {
                return $versionStatuses[$status];
            }
            return null;
        }

        return $versionStatuses;
    }

    /**
     * @param mixed $ids
     */
    public function setStoreIds($ids)
    {
        if (is_array($ids)) {
            return $this->addStores($ids);
        }
        return $this->setData('store_ids', $ids);
    }

    /**
     * Set the stores to be installed on.
     * If module is already installed on some stores, then these stores
     * will be merged with received ids
     *
     * @param array $ids
     * @return TM_Core_Model_Module
     */
    public function addStores(array $ids)
    {
        $installedStores = $this->getStoreIds();
        if (!count($installedStores)) {
            $installedStores = array();
        } elseif (!is_array($installedStores)) {
            $installedStores = explode(',', $installedStores);
        }
        $merged = array_merge($ids, $installedStores);
        $this->setData('store_ids', implode(',', array_unique($merged)));

        return $this;
    }

    /**
     * Retieve store ids as array
     *
     * @return array
     */
    public function getStores()
    {
        $ids = $this->getStoreIds();
        if (!count($ids)) {
            return array();
        } elseif (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        return $ids;
    }

    public function isInstalled()
    {
        return false;// we always can install the extension to the new stores
        return $this->getLicenseKey();
    }

    public function hasUpgradesToRun()
    {
        return (bool) $this->getUpgradesToRun();
    }

    /**
     * Retrive the list of all module upgrade filenames
     * sorted by version_compare
     *
     * @return array
     */
    public function getUpgrades()
    {
        try {
            $dir = new DirectoryIterator($this->getUpgradesPath());
        } catch (Exception $e) {
            // module doesn't has upgrades
            return array();
        }

        $upgrades = array();
        foreach ($dir as $file) {
            $file = $file->getFilename();
            if (false === strstr($file, '.php')) {
                continue;
            }
            $upgrades[] = substr($file, 0, -4);
        }
        usort($upgrades, 'version_compare');
        return $upgrades;
    }

    /**
     * Retrieve the list of not installed upgrade filenames
     * sorted by version_compare.
     * The list could be filtered with optional from and to parameters.
     * These parameters are usefull, when the module is installed and new upgrades
     * are available
     *
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getUpgradesToRun($from = null, $to = null)
    {
        if (null === $from) {
            $from = $this->getDataVersion();
        }

        $upgrades = array();
        foreach ($this->getUpgrades() as $upgradeVersion) {
            if (version_compare($from, $upgradeVersion) >= 0) {
                continue;
            }
            $upgrades[] = $upgradeVersion;
        }

        return $upgrades;
    }

    /**
     * Run the module upgrades. Depends run first.
     *
     * @param $from
     * @param @to
     * @return void
     */
    public function up($from = null, $to = null)
    {
        $stores = $this->getStores();
        if (!count($stores)) {
            return;
        }

        foreach ($this->getDepends() as $moduleCode) {
            $module = $this->_getModuleObject($moduleCode);
            foreach ($module->getUpgradesToRun() as $version) {
                $module->getUpgradeObject($version)->run();
                $module->setDataVersion($version)->save();
            }
        }

        foreach ($this->getUpgradesToRun($from, $to) as $version) {
            $this->getUpgradeObject($version)->run();
            $this->setDataVersion($version)->save();
        }
    }

    /**
     * Retrieve singleton instance of error logger, used in upgrade file
     * to write errors and module controller to read them.
     *
     * @return TM_Core_Model_Module_MessageLogger
     */
    public function getMessageLogger()
    {
        if (null === self::$_messageLogger) {
            self::$_messageLogger = Mage::getSingleton('tmcore/module_messageLogger');
        }
        return self::$_messageLogger;
    }

    /**
     * This method is used to get the operations for preview only
     *
     * @see getUpgradeOperationsAsString
     * @return array
     */
    protected function _getUpgradeOperations($from = null, $to = null)
    {
        $operations = array();

        foreach ($this->getDepends() as $moduleCode) {
            $module = $this->_getModuleObject($moduleCode);
            $operations[$moduleCode] = array();
            foreach ($module->getUpgradesToRun() as $upgrade) {
                if (!isset($operations[$moduleCode][$upgrade])) {
                    $operations[$moduleCode][$upgrade] = array();
                }
                $operations[$moduleCode][$upgrade] = array_merge_recursive(
                    $operations[$moduleCode][$upgrade],
                    $module->getUpgradeObject($upgrade)->getOperations()
                );
            }
        }
        $operations[$this->getId()] = array();
        foreach ($this->getUpgradesToRun($from, $to) as $upgrade) {
            if (!isset($operations[$this->getId()][$upgrade])) {
                $operations[$this->getId()][$upgrade] = array();
            }
            $operations[$this->getId()][$upgrade] = array_merge_recursive(
                $operations[$this->getId()][$upgrade],
                $this->getUpgradeObject($upgrade)->getOperations()
            );
        }
        return $operations;
    }

    /**
     * Retrieve upgrade operations as formatted string.
     * Used to show the upgrade operations inside textarea field.
     *
     * @param $from
     * @param @to
     * @return string
     */
    public function getUpgradeOperationsAsString($from = null, $to = null)
    {
        $result = array();
        $indent = '    ';
        foreach ($this->_getUpgradeOperations($from, $to) as $module => $versions) {
            $result[] = $module;
            foreach ($versions as $version => $sections) {
                $result[] = $indent . $version;
                foreach ($sections as $section => $operations) {
                    $result[] = str_repeat($indent, 2) . $section;
                    foreach ($operations as $key => $value) {
                        $result[] = str_repeat($indent, 3) . $key . ': ' . $value;
                    }
                }
            }
        }
        return implode("\n", $result);
    }

    /**
     * Retrieve upgrade class name from version string:
     * 1.0.0 => ModuleCode_Upgrade_1_0_0
     *
     * @param string $version
     * @return string Class name
     */
    protected function _getUpgradeClassName($version)
    {
        $version = ucwords(preg_replace("/\W+/", " ", $version));
        $version = str_replace(' ', '_', $version);
        return $this->getId() . '_Upgrade_' . $version;
    }

    /**
     * Returns upgrade class instance by given version
     *
     * @param string $version
     * @return TM_Core_Model_Module_Upgrade
     */
    public function getUpgradeObject($version)
    {
        require_once $this->getUpgradesPath() . "/{$version}.php";
        $className = $this->_getUpgradeClassName($version);
        $upgrade = new $className();
        $upgrade->setStoreIds($this->getStores())->setModule($this);
        return $upgrade;
    }

    /**
     * Retrieve module upgrade directory
     *
     * @return string
     */
    public function getUpgradesPath()
    {
         return Mage::getBaseDir('code')
            . DS
            . $this->_getData('codePool')
            . DS
            . uc_words($this->getId(), DS)
            . DS
            . 'upgrades';
    }

    protected function _getModuleObject($code)
    {
        return Mage::getModel('tmcore/module')
            ->load($code)
            ->addStores($this->getStores());
    }
}
