<?php

class TM_Core_Block_Adminhtml_Module_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('moduleGrid');
        $this->setDefaultSort('release_date');
        $this->setDefaultDir('DESC');
        $this->setDefaultFilter(array(
            'version' => TM_Core_Block_Adminhtml_Module_Grid_Filter_Version::VERSION_AVAILABLE
        ));
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('module_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('tmcore/module_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'header' => Mage::helper('tmcore')->__('Code'),
            'align'  => 'left',
            'index'  => 'code'
        ));

        $this->addColumn('version', array(
            'header' => Mage::helper('tmcore')->__('Version'),
            'align'  => 'center',
            'filter' => 'tmcore/adminhtml_module_grid_filter_version',
            'renderer' => 'tmcore/adminhtml_module_grid_renderer_version',
            'index'  => 'version',
            'width'  => '150px'
        ));

        $this->addColumn('release_date', array(
            'header' => Mage::helper('tmcore')->__('Latest Release Date'),
            'index'  => 'release_date',
            'filter' => false,
            'width'  => '180px',
            'type'   => 'datetime',
        ));

        $this->addColumn('actions', array(
            'header'   => Mage::helper('tmcore')->__('Actions'),
            'width'    => '160px',
            'filter'   => false,
            'sortable' => false,
            'renderer' => 'tmcore/adminhtml_module_grid_renderer_actions'
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }
}
