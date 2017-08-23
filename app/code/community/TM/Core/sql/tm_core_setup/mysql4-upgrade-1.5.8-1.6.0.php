<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('tmcore/module');

$installer->getConnection()
    ->addColumn(
        $tableName,
        'name',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 50,
            'nullable' => true,
            'default'  => null,
            'after'    => 'code',
            'comment'  => 'Package Name'
        )
    );

$installer->getConnection()
    ->addColumn(
        $tableName,
        'version',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 50,
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Installed Version'
        )
    );

$installer->getConnection()
    ->addColumn(
        $tableName,
        'latest_version',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 50,
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Latest Version'
        )
    );

$installer->getConnection()
    ->addColumn(
        $tableName,
        'release_date',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Latest Release Date'
        )
    );

$installer->endSetup();
