<?php

class TM_Core_Model_Resource_Module_RemoteCollection extends TM_Core_Model_Resource_Module_Collection_Abstract
{
    const XML_FEED_URL_PATH = 'tmcore/modules/feed_url';

    const RESPONSE_CACHE_KEY = 'tm_components_remote_response';

    public function getMapping()
    {
        return array(
            'name' => 'name',
            'code' => 'code',
            'description' => 'description',
            'keywords' => 'keywords',
            'version' => 'latest_version',
            'type' => 'type',
            'time' => 'release_date',
            'extra.tm.links.store' => 'link',
            'extra.tm.links.docs' => 'docs_link',
            'extra.tm.links.download' => 'download_link',
            'extra.tm.links.changelog' => 'changelog_link',
            'extra.tm.links.marketplace' => 'marketplace_link',
            'extra.tm.links.identity_key' => 'identity_key_link',
            'extra.tm.purchase_code' => 'purchase_code',
        );
    }

    /**
     * Lauch data collecting
     *
     * @return array
     */
    protected function _loadModules()
    {
        try {
            if (!$responseBody = Mage::app()->loadCache(self::RESPONSE_CACHE_KEY)) {
                $responseBody = $this->_fetch($this->_getFeedUri());
                Mage::app()->saveCache($responseBody, self::RESPONSE_CACHE_KEY);
            }
            $response = Mage::helper('core')->jsonDecode($responseBody);
        } catch (\Exception $e) {
            $response = array();
            // Swissup_Subscription will be added below - used by
            // subscription activation module
        }

        if (!is_array($response)) {
            $response = array();
        }

        $modules = array();
        if (!empty($response['packages'])) {
            foreach ($response['packages'] as $packageName => $info) {
                $versions = array_keys($info);
                $latestVersion = array_reduce($versions, array($this, '_getNewerVersion'));
                if (!empty($info[$latestVersion]['type']) &&
                    $info[$latestVersion]['type'] === 'metapackage') {

                    continue;
                }
                // if ($latestVersion === 'dev-master') {
                //     continue;
                // }
                $code = $this->_packageNameToCode($packageName);
                $modules[$code] = $info[$latestVersion];
                $modules[$code]['code'] = $code;
                if (isset($info['dev-master']['extra']['tm'])) {
                    $modules[$code]['extra']['tm'] = $info['dev-master']['extra']['tm'];
                }
            }
        }
        $modules['Swissup_Subscription'] = array(
            'name'          => 'swissup/subscription',
            'code'          => 'Swissup_Subscription',
            'type'          => 'subscription-plan',
            'description'   => 'SwissUpLabs Modules Subscription',
            'version'       => '',
            'extra' => array(
                'swissup' => array(
                    'links' => array(
                        'store' => 'https://swissuplabs.com',
                        'download' => 'https://swissuplabs.com/subscription/customer/products/',
                        'identity_key' => 'https://swissuplabs.com/license/customer/identity/'
                    )
                )
            )
        );

        // fix for argento themes
        if (isset($modules['TM_Argento'])) {
            $argento = array(
                'TM_ArgentoArgento',
                'TM_ArgentoFlat',
                'TM_ArgentoMall',
                'TM_ArgentoPure',
                'TM_ArgentoPure2',
                'TM_ArgentoLuxury',
            );
            foreach ($argento as $theme) {
                if (!isset($modules[$theme])) {
                    $modules[$theme] = array();
                }

                $modules[$theme] = array_merge(
                    $modules['TM_Argento'],
                    $modules[$theme]
                );
                $modules[$theme]['code'] = $theme;
            }
            // remove installer link from metapackage
            unset($modules['TM_Argento']['extra']);
        }

        $result = array();
        foreach ($modules as $code => $config) {
            foreach ($this->getMapping() as $source => $destination) {
                $value = $config;
                foreach (explode('.', $source) as $key) {
                    if (!isset($value[$key])) {
                        continue 2;
                    }
                    $value = $value[$key];
                }

                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $result[$code][$destination] = $value;
            }
        }

        foreach ($result as $moduleName => $values) {
            $result[$moduleName]['id'] = $moduleName;
        }

        return $result;
    }

    /**
     * Get newer version between two of them
     *
     * @param  [type] $carry [description]
     * @param  [type] $item  [description]
     * @return string
     */
    protected function _getNewerVersion($carry, $item)
    {
        if (version_compare($carry, $item) === -1) {
            return $item;
        }
        return $carry;
    }

    /**
     * Trnasform composer-like package code to magento module code
     *
     * @param  string $packageName
     * @return string
     */
    public function _packageNameToCode($packageName)
    {
        $mapping = array(
            'tm/argento_mage2cloud' => 'TM_ArgentoMage2Cloud',
            'tm/argento_tm'         => 'TM_ArgentoTM',
            'tm/cdn'                => 'TM_CDN',
            'tm/countdowntimer'     => 'TM_CountdownTimer',
            'tm/dailydeals'         => 'TM_DailyDeals',
            'tm/easycatalogimg'     => 'TM_EasyCatalogImg',
            'tm/easycolorswatches'  => 'TM_EasyColorSwatches',
            'tm/easyflags'          => 'TM_EasyFlags',
            'tm/easylightbox'       => 'TM_EasyLightbox',
            'tm/easynavigation'     => 'TM_EasyNavigation',
            'tm/easytabs'           => 'TM_EasyTabs',
            'tm/facebooklb'         => 'TM_FacebookLB',
            'tm/firecheckout'       => 'TM_FireCheckout',
            'tm/lightboxpro'        => 'TM_LightboxPro',
            'tm/mobileswitcher'     => 'TM_MobileSwitcher',
            'tm/newsletterbooster'  => 'TM_NewsletterBooster',
            'tm/orderattachment'    => 'TM_OrderAttachment',
            'tm/productvideos'      => 'TM_ProductVideos',
            'tm/quickshopping'      => 'TM_QuickShopping',
            'tm/richsnippets'       => 'TM_RichSnippets',
            'tm/smartsuggest'       => 'TM_SmartSuggest',
            'tm/suggestpage'        => 'TM_SuggestPage',
            'tm/demo-deployer'      => 'TM_Deployer',
            'tm/helpdesk'           => 'TM_Helpmate',
            'tm/socialsuite'        => 'TM_SocialSuite',
        );

        if (isset($mapping[$packageName])) {
            return $mapping[$packageName];
        }

        list($vendor, $module) = explode('/', $packageName, 2);
        if ($vendor === 'tm') {
            $vendor = 'TM';
        } else {
            $vendor = ucfirst($vendor);
        }

        $module = ucwords(str_replace(array('_', '-'), ' ', $module));
        $module = str_replace(' ', '', $module);

        return $vendor . '_' . $module;
    }

    /**
     * Make a http request and return response body
     *
     * @param  string $url
     * @return string
     */
    protected function _fetch($url)
    {
        $client = new Zend_Http_Client();
        $adapter = new Zend_Http_Client_Adapter_Curl();
        $client->setAdapter($adapter);
        $client->setUri($url);
        $client->setConfig(array(
            'maxredirects' => 5,
            'timeout' => 30
        ));
        $client->setParameterGet('domain', Mage::app()->getRequest()->getHttpHost());
        return $client->request()->getBody();
    }

    /**
     * Get feed url from satis repository.
     *
     * To do that we send a request to http://tmhub.github.io/packages/packages.json,
     * which returns actual packages list url: http://tmhub.github.io/packages/include/all${sha1}.json
     *
     * @return string
     */
    protected function _getFeedUri()
    {
        $useHttps = Mage::getStoreConfigFlag(TM_Core_Model_Module::XML_USE_HTTPS_PATH);
        $url = Mage::getStoreConfig(self::XML_FEED_URL_PATH);

        // http://tmhub.github.io/packages/packages.json
        $url = ($useHttps ? 'https://' : 'http://') . $url;

        $response = $this->_fetch($url . '/packages.json');
        $response = Mage::helper('core')->jsonDecode($response);
        if (!is_array($response) || !isset($response['includes'])) {
            return false;
        }

        // http://tmhub.github.io/packages/include/all${sha1}.json
        return $url . '/' . key($response['includes']);
    }
}
