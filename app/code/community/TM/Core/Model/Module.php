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

    protected function _construct()
    {
        $this->_init('tmcore/module');
    }

    public function load($id, $field=null)
    {
        parent::load($id, $field);

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
     * Merge new_store_ids and store_ids arrays
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $oldStores = $this->getOldStores();
        $newStores = $this->getNewStoreIds();
        if (is_array($newStores)) {
            $stores = array_merge($oldStores, $newStores);
            $this->setStoreIds(implode(',', array_unique($stores)));
        }
        return parent::_beforeSave();
    }


    /**
     * Set the stores, where the module should be installed or reinstalled
     *
     * @param array $ids
     * @return TM_Core_Model_Module
     */
    public function setNewStores(array $ids)
    {
        // $oldStores = $this->getOldStores();
        // $newStores = array_diff($ids, $oldStores);
        // $this->setData('new_store_ids', array_unique($newStores));

        $this->setData('new_store_ids', array_unique($ids));
        return $this;
    }

    /**
     * Retieve store ids, where the module is already installed
     *
     * @return array
     */
    public function getOldStores()
    {
        $ids = $this->getStoreIds();
        if (null === $ids || '' === $ids) {
            return array();
        }
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        return $ids;
    }

    /**
     * Retieve store ids, where the module is already installed
     *
     * @return array
     */
    public function getStores()
    {
        return $this->getOldStores();
    }

    /**
     * Retrieve store ids to install module on
     *
     * @return array
     */
    public function getNewStores()
    {
        return $this->getNewStoreIds();
    }

    public function isInstalled()
    {
        return false;// we always can install the extension to the new stores
        // return $this->getLicenseKey();
    }

    /**
     * Checks is the upgrades directory is exists in the module
     *
     * @return boolean
     */
    public function hasUpgradesDir()
    {
        return is_readable($this->getUpgradesPath());
    }

    /**
     * Retrieve the list of not installed upgrade filenames
     * sorted by version_compare.
     * The list could be filtered with optional from and to parameters.
     * These parameters are usefull, when the module is installed and new upgrades
     * are available
     *
     * @param string $from
     * @return array
     */
    public function getUpgradesToRun($from = null)
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
     * Retrive the list of all module upgrade filenames
     * sorted by version_compare
     *
     * @return array
     */
    public function getUpgrades()
    {
        $upgrades = $this->getData('upgrades');
        if (is_array($upgrades)) {
            return $upgrades;
        }

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
        $this->setData('upgrades', $upgrades);
        return $upgrades;
    }

    /**
     * Run the module upgrades. Depends run first.
     *
     * @return void
     */
    public function up()
    {
        $oldStores = $this->getOldStores(); // update to newest data_version
        $newStores = $this->getNewStores(); // run all upgrade files
        if (!count($oldStores) && !count($newStores)) {
            return;
        }

        foreach ($this->getDepends() as $moduleCode) {
            $this->_getModuleObject($moduleCode)->up();
        }
        $saved = false;

        // upgrade currently installed version to the latest data_version
        if (count($oldStores)) {
            foreach ($this->getUpgradesToRun() as $version) {
                // customer able to skip upgrading data of installed modules
                if (!$this->getSkipUpgrade()) {
                    $this->getUpgradeObject($version)
                        ->setStoreIds($oldStores)
                        ->upgrade();
                }
                $this->setDataVersion($version)->save();
                $saved = true;
            }
        }

        // install module to the new stores
        if (count($newStores)) {
            foreach ($this->getUpgradesToRun(0) as $version) {
                $this->getUpgradeObject($version)
                    ->setStoreIds($newStores)
                    ->upgrade();
                $this->setDataVersion($version)->save();
                $saved = true;
            }
        }

        if (!$saved) {
            $this->save(); // identity key could be updated without running the upgrades
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
        $upgrade->setModule($this);
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

    /**
     * Returns loded module object with copied new_store_ids and skip_upgrade
     * instructions into it
     *
     * @return TM_Core_Model_Module
     */
    protected function _getModuleObject($code)
    {
        $module = Mage::getModel('tmcore/module')->load($code)
            ->setNewStores($this->getNewStores())
            ->setSkipUpgrade($this->getSkipUpgrade());

        if (!$module->getIdentityKey()) {
            // dependent modules will have the same license if not exists
            $module->setIdentityKey($this->getIdentityKey());
        }

        return $module;
    }
}
