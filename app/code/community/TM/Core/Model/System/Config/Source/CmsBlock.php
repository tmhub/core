<?php

/**
 * Cms block names with store ids
 */
class TM_Core_Model_System_Config_Source_CmsBlock
{
    public function toOptionArray()
    {
        $collection = Mage::getResourceModel('cms/block_collection')
            ->addFieldToFilter('is_active', 1)
            ->addOrder('title', 'ASC');

        $collection->getSelect()
            ->join(
                array('store_table' => $collection->getTable('cms/block_store')),
                'main_table.block_id = store_table.block_id',
                array(
                    'store_ids' => new Zend_Db_Expr(
                        "GROUP_CONCAT(store_table.store_id ORDER BY store_table.store_id ASC SEPARATOR ', ')"
                    )
                )
            )
            ->group('main_table.block_id');

        $blocks = array();
        foreach ($collection as $block) {
            $blocks[] = array(
                'label' => $block->getTitle() . " (" . $block->getStoreIds() . ")",
                'value' => $block->getId()
            );
        }
        array_unshift($blocks, array(
            'value' => 0,
            'label' => Mage::helper('adminhtml')->__('No')
        ));
        return $blocks;
    }
}
