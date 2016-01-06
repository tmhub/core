<?php

class TM_Core_Helper_Debug extends Mage_Core_Helper_Abstract
{
    const POPUP_NAME = 'tmcore_debug_popup';

    public function preparePopup($text, $title = 'Debug Information')
    {
        $helper = Mage::helper('core');

        Mage::app()->getLayout()
            ->createBlock('core/text')
            ->setNameInLayout(self::POPUP_NAME)
            ->setText(
                '<div id="'.self::POPUP_NAME.'" style="display:none">'
                    . '<pre>'
                    . $helper->escapeHtml($text)
                    . '</pre>'
                . '</div>'
            );

        $title = $helper->escapeHtml($title);
        return sprintf(
            "<a href='#' onclick=\"%s\">%s</a>",
            "tmcoreWindow.update($('".self::POPUP_NAME."').innerHTML, '{$title}').show()",
            Mage::helper('tmcore')->__('Show response')
        );
    }
}
