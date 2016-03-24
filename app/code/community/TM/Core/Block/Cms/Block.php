<?php
/**
 * Rewrite Mage_Cms_Block_Block class to prevent CMS Static blocks from caching.
 * TM Themes and Extensions use static blocks to output dynamic data.
 */
class TM_Core_Block_Cms_Block extends Mage_Cms_Block_Block
{
	/**
     * No cache for CMS blocks
     *
     * @return null
     */
	protected function _construct()
    {
    	parent::_construct();
        $this->unsCacheTags();
        $this->unsCacheLifetime();
    }
}
