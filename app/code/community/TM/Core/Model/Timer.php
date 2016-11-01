<?php

/**
 * Usage example:
 *
 *  1. $this->_timer = Mage::getModel('tmcore/timer', array('name' => 'tm_crawler'));
 *
 *  2. $this->_timer->startOrResume();
 *
 *  3. $limit = $this->_timer->getTimeLimit() / 3;
 *     if ($this->_timer->getElapsedSecs() >= $limit) {
 *         return;
 *     }
 */
class TM_Core_Model_Timer extends Varien_Object
{
    protected $_timers = array();

    public function start($reset = false)
    {
        if ($reset) {
            $this->_timers[$name]['start'] = microtime(true);
        } else {
            $this->startOrResume();
        }
    }

    public function stop()
    {
        $this->_timers[$this->getName()]['stop'] = microtime(true);
    }

    public function startOrResume()
    {
        $name = $this->getName();
        if (isset($this->_timers[$name])) {
            return;
        }
        $this->_timers[$name]['start'] = microtime(true);
    }

    public function getElapsedSecs()
    {
        $now = microtime(true);
        if (isset($this->_timers[$this->getName()]['stop'])) {
            return $this->_timers[$this->getName()]['stop']
                - $this->_timers[$this->getName()]['start'];
        }
        return $now - $this->_timers[$this->getName()]['start'];
    }

    /**
     * Get max execution time
     *
     * @return int
     */
    public function getTimeLimit()
    {
        $time = @ini_get('max_execution_time');
        if (empty($time)) {
            $time = 30;
        }
        return $time;
    }

    /**
     * Increase max_execution_time
     *
     * @param  int $value Seconds
     * @return mixed
     */
    public function increaseTimeLimitTo($value)
    {
        $timeLimit = $this->getTimeLimit();
        if ($value <= $timeLimit) {
            return $timeLimit;
        }
        return @ini_set('max_execution_time', $value);
    }
}
