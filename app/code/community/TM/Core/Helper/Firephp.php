<?php

class TM_Core_Helper_Firephp extends Mage_Core_Helper_Abstract
{
    /*
    Mage::helper('tmcore/firephp')->send(Mage::helper('core/http')->getRemoteAddr());

    Mage::helper('tmcore/firephp')->send(__METHOD__ . __LINE__);

    Varien_Profiler::enable();
    $timerName = __METHOD__;
    Varien_Profiler::start($timerName);

    Varien_Profiler::stop($timerName);
    Mage::helper('tmcore/firephp')->debug(Varien_Profiler::fetch($timerName));
    */

    /**
     * @var Zend_Wildfire_Channel_HttpHeaders
     */
    private $channel = null;

    /**
     * @var Zend_Wildfire_Plugin_FirePhp
     */
    private $firephp = null;


    public function __construct()
    {
        //compatible with older Zend in older Magento(1.3.2.x)
        $this->getChannel()
            ->setRequest(Mage::app()->getRequest())
            ->setResponse(Mage::app()->getResponse());

        $this->setOption('traceOffset', 3);
        $this->setOption('maxTraceDepth', 25);
        $this->setOption('maxObjectDepth', 1);
    }

    /**
     * @return Zend_Wildfire_Channel_HttpHeaders
     */
    private function getChannel()
    {
        if (null == $this->channel) {
            $this->channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        }
        return $this->channel;
    }

    /**
     * @return Zend_Wildfire_Plugin_FirePhp
     */
    private function getFirePhp()
    {
        if (null == $this->firephp) {
            $this->firephp = Zend_Wildfire_Plugin_FirePhp::getInstance();
        }
        return $this->firephp;
    }

    /**
     * Set a single option
     *
     * @param  string $key The name of the option
     * @param  mixed $value The value of the option
     * @return mixed The previous value of the option
     */
    public function setOption($key, $value)
    {
        return $this->getFirePhp()->setOption($key, $value);
    }

    /**
     * Logs variables to the Firebug Console
     * via HTTP response headers and the FirePHP Firefox Extension.
     *
     * @param  mixed  $var   The variable to log.
     * @param  string  $label OPTIONAL Label to prepend to the log event.
     * @param  string  $style  OPTIONAL Style of the log event.
     * @param  array  $options OPTIONAL Options to change how messages are processed and sent
     * @return boolean Returns TRUE if the variable was added to the response headers or buffered.
     * @throws Zend_Wildfire_Exception
     */
    private function _send($var, $label = null, $style = null, $options = array())
    {
        $return = $this->getFirePhp()->send($var, $label, $style, $options);
        Mage::app()->getResponse()->sendHeaders();
        $this->getChannel()->flush();
        return $return;
    }

    public function send($var, $label = '', $style = Zend_Wildfire_Plugin_FirePhp::LOG, $options = array('traceOffset' => 3))
    {
        return $this->_send($var, $label, $style, $options);
    }

    /**
     * @param  mixed  $var   The variable to log.
     * @param  string  $label OPTIONAL Label to prepend to the log event.
     * @return boolean Returns TRUE if the variable was added to the response headers or buffered.
     */
    public function log($var, $label = '')
    {
        return $this->_send($var, $label, Zend_Wildfire_Plugin_FirePhp::LOG);
    }

    /**
     * @param  mixed  $var   The variable to log.
     * @param  string  $label OPTIONAL Label to prepend to the log event.
     * @return boolean Returns TRUE if the variable was added to the response headers or buffered.
     */
    public function info($var, $label = '')
    {
        return $this->_send($var, $label, Zend_Wildfire_Plugin_FirePhp::INFO);
    }

    /**
     * @param  mixed  $var   The variable to log.
     * @param  string  $label OPTIONAL Label to prepend to the log event.
     * @return boolean Returns TRUE if the variable was added to the response headers or buffered.
     */
    public function warn($var, $label = '')
    {
        return $this->_send($var, $label, Zend_Wildfire_Plugin_FirePhp::WARN);
    }

    /**
     * @param  mixed  $var   The variable to log.
     * @param  string  $label OPTIONAL Label to prepend to the log event.
     * @return boolean Returns TRUE if the variable was added to the response headers or buffered.
     */
    public function error($var, $label = '')
    {
        return $this->_send($var, $label, Zend_Wildfire_Plugin_FirePhp::ERROR);
    }

    /**
     * @param  mixed  $var   The variable to log.
     * @param  string  $label OPTIONAL Label to prepend to the log event.
     * @return boolean Returns TRUE if the variable was added to the response headers or buffered.
     */
    public function trace($var = '', $label = '')
    {
        return $this->_send($var, $label, Zend_Wildfire_Plugin_FirePhp::TRACE);
    }

    /**
     * @param  mixed  $var   The variable to log.
     * @param  string  $label OPTIONAL Label to prepend to the log event.
     * @param  string  $style  OPTIONAL Style of the log event.
     * @return boolean Returns TRUE if the variable was added to the response headers or buffered.
     */
    public function debug($var, $label = '', $style = Zend_Wildfire_Plugin_FirePhp::LOG)
    {
        if ($var instanceof Varien_Object) {
            $var = $var->debug();
        }

        return $this->_send($var, $label, $style);
    }

    /**
     * @param  mixed  $var   The variable to log.
     * @param  string  $label OPTIONAL Label to prepend to the log event.
     * @param  string  $style  OPTIONAL Style of the log event.
     * @return boolean Returns TRUE if the variable was added to the response headers or buffered.
     */
    public function dump($var, $label = '', $style = Zend_Wildfire_Plugin_FirePhp::LOG)
    {
        return $this->debug($var, $label, $style);
    }

    /**
     * @param  mixed  $var   The variable to log.
     * @param  string  $label OPTIONAL Label to prepend to the log event.
     * @return boolean Returns TRUE if the variable was added to the response headers or buffered.
     */
    public function getClass($var, $label = '')
    {
        $var = is_object($var) ? get_class($var) : $var;
        return $this->info($var, $label, Zend_Wildfire_Plugin_FirePhp::LOG);
    }

    /**
     * Starts a group in the Firebug Console
     *
     * @param string $title The title of the group
     * @param array $options OPTIONAL Setting 'Collapsed' to true will initialize group collapsed instead of expanded
     * @return TRUE if the group instruction was added to the response headers or buffered.
     */
    public function group($title, $options = array())
    {
        return $this->_send(null, $title, Zend_Wildfire_Plugin_FirePhp::GROUP_START, $options);
    }

    /**
     * Ends a group in the Firebug Console
     *
     * @return TRUE if the group instruction was added to the response headers or buffered.
     */
    public function groupEnd()
    {
        return $this->_send(null, null, Zend_Wildfire_Plugin_FirePhp::GROUP_END);
    }
}
