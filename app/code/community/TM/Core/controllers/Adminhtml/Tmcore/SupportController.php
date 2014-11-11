<?php

class TM_Core_Adminhtml_Tmcore_SupportController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/tmcore_module')
            ->_addBreadcrumb('Templates Master', 'Templates Master')
            ->_addBreadcrumb(Mage::helper('tmcore')->__('Support'), Mage::helper('tmcore')->__('Support'));
        return $this;
    }

    /**
     *
     * @return string (uri)
     */
    protected function _getApiHost()
    {
//        return 'http://mage.local';
        return Mage::getStoreConfig('tmcore/troubleshooting/url');
    }

    /**
     *
     * @return Zend_Oauth_Client
     */
    protected function _getRestApiClient()
    {
        $oAuthClient = Mage::getModel('tmcore/oauth_client');
        $params = $oAuthClient->getConfigFromSession();
        if (!$params) {
            return false;
        }
        $oAuthClient->init($params);
        $state = $oAuthClient->authenticate();
        if ($state == TM_Core_Model_Oauth_Client::OAUTH_STATE_ACCESS_TOKEN) {
            $accessToken = $oAuthClient->getAuthorizedToken();
        }
        $restClient = $accessToken->getHttpClient($params);

        return $restClient;
    }

    /**
     *
     * @param type $response
     * @return boolean
     */
    protected function _prepareApiRestResponseErrorMessages($response)
    {
        if (!is_object($response) || !property_exists($response, 'messages')) {
            return false;
        }
        $messages = $response->messages;
        if ($messages) {
            $errors = $messages->error;
            if ($errors) {
                foreach ($errors as $error) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        $error->message
                    );
                }
//                $this->_redirectReferer();
//                die;
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param string $uri
     * @return \Varien_Object|\TM_Core_Model_Resource_Support_Collection
     */
    protected function _getRestApiData($uri)
    {
        $restClient = $this->_getRestApiClient();

        if (!$restClient) {
            return false;
        }

        $restClient->setHeaders('Accept', 'application/json');
        $restClient->setMethod(Zend_Http_Client::GET);

        $host = $this->_getApiHost();
        $restClient->setUri($host . '/api/rest' . $uri);

        $response = $restClient->request();
        $_items = json_decode($response->getBody());

        $this->_prepareApiRestResponseErrorMessages($_items);

        if (is_array($_items)) {
            $collection = new TM_Core_Model_Resource_Support_Collection();
            foreach ($_items as &$_item) {
                $_item = (array)$_item;
            }
            $collection->setFromArray($_items);
            return $collection;
        }
        $object = new Varien_Object();
        $object->setData((array)$_items);
        return $object;
    }

    /**
     *
     * @param type $uri
     * @param type $params
     * @return type
     */
    protected function _setRestApiData($uri, $params)
    {
        $restClient = $this->_getRestApiClient();

        if (!$restClient) {
            return false;
        }

        $restClient->setHeaders('Accept', 'application/json');
        $restClient->setHeaders('Content-Type','application/json');
        $restClient->setEncType('application/json');
        $restClient->setMethod(Zend_Http_Client::POST);

        $host = $this->_getApiHost();
        $restClient->setUri($host . '/api/rest' . $uri);

        $restClient->setRawData(json_encode($params));

        $response = $restClient->request();
        $object = json_decode($response->getBody());

        $this->_prepareApiRestResponseErrorMessages($object);

        return $object;
    }

    public function oauthAction()
    {
        $host = $this->_getApiHost();
        $consumerKey = Mage::getStoreConfig('tmcore/troubleshooting/consumer_key');
        $consumerSecret = Mage::getStoreConfig('tmcore/troubleshooting/consumer_secret');
        //Basic parameters that need to be provided for oAuth authentication
        //on Magento
        $params = array(
            'siteUrl'         => "{$host}/oauth",
            'requestTokenUrl' => "{$host}/oauth/initiate",
            'accessTokenUrl'  => "{$host}/oauth/token",
            'authorizeUrl'    => "{$host}/oauth/authorize",
//            'authorizeUrl'    => "{$magentohost}/admin/oauth_authorize", //This URL is used only if we authenticate as Admin user type
            'consumerKey'     => $consumerKey, //Consumer key registered in server administration
            'consumerSecret'  => $consumerSecret, //Consumer secret registered in server administration
            'callbackUrl'     => $this->getUrl('*/*/index')//Url of callback action below
        );
        $oAuthClient = Mage::getModel('tmcore/oauth_client');
        $oAuthClient->reset();
        $oAuthClient->init($params);
        $oAuthClient->authenticate();
    }

    public function indexAction()
    {
        $collection = $this->_getRestApiData('/helpdesk/tickets');

        if (empty($collection)) {
            return $this->_redirect('*/*/oauth');
        }
        Mage::register('tmcore_support_collection', $collection);

        $model = new Varien_Object();

        $model->setDepartmets(
            $this->_getRestApiData("/helpdesk/departments")
        );

        $model->setStatuses(
            $this->_getRestApiData("/helpdesk/statuses")
        );

        $model->setPriorities(
            $this->_getRestApiData("/helpdesk/priorities")
        );

        Mage::register('tmcore_support', $model);

        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction()
    {
        $ticketId = $this->getRequest()->getParam('ticket_id');

        $model = $this->_getRestApiData("/helpdesk/tickets/{$ticketId}");

        if (empty($model)) {
            return $this->_redirect('*/*/oauth');
        }

        $model->setTheards(
            $this->_getRestApiData("/helpdesk/tickets/{$ticketId}/theards")
        );

        $model->setDepartmets(
            $this->_getRestApiData("/helpdesk/departments")
        );

        $model->setStatuses(
            $this->_getRestApiData("/helpdesk/statuses")
        );

        $model->setPriorities(
            $this->_getRestApiData("/helpdesk/priorities")
        );

        Mage::register('tmcore_support', $model);

        $this->_initAction();
        $this->renderLayout();
    }

    public function newAction()
    {
        $model = new Varien_Object();
        $model->setDepartmets(
            $this->_getRestApiData("/helpdesk/departments")
        );

        $model->setStatuses(
            $this->_getRestApiData("/helpdesk/statuses")
        );

        $model->setPriorities(
            $this->_getRestApiData("/helpdesk/priorities")
        );

        Mage::register('tmcore_support', $model);

        $this->_initAction();
        $this->renderLayout();
    }

    public function saveAction()
    {
        $params = $this->getRequest()->getParams();
        $ticketId = $this->getRequest()->getParam('id');
//        $text = $this->getRequest()->getParam('text');
        if (empty($params['text'])) {
            throw new Exception('text is null');
        }
        //save ticket
        if (empty($ticketId)) {
            $response = $this->_setRestApiData("/helpdesk/tickets", array(
                'title'         => $params['title'],
                'department_id' => $params['department_id'],
                'priority'      => $params['priority'],
                'text'          => $params['text'],
            ));
        } else { //save theard
            $response = $this->_setRestApiData(
                "/helpdesk/tickets/{$ticketId}/theards",
                array('text' => $params['text'])
            );
        }
//        $messages = $response->messages;
//        if ($messages) {
//            $errors = $messages->error;
//            if ($errors) {
//                foreach ($errors as $error) {
//                    Mage::getSingleton('adminhtml/session')->addError($error->message);
//                }
//            }
//        }
        Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('tmcore')->__('Item was successfully saved')
        );
        $this->_redirectReferer();
    }
}
