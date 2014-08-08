<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * X-Cart
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the software license agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.x-cart.com/license-agreement.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@x-cart.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not modify this file if you wish to upgrade X-Cart to newer versions
 * in the future. If you wish to customize X-Cart for your needs please
 * refer to http://www.x-cart.com/ for more information.
 *
 * @category  X-Cart 5
 * @author    Qualiteam software Ltd <info@x-cart.com>
 * @copyright Copyright (c) 2011-2013 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Upgrade;

/**
 * Logger
 */
class Logger extends \XLite\Base\Singleton
{
    /**
     * Clear log file
     *
     * @return boolean
     */
    public function clear()
    {
        return \Includes\Utils\FileManager::deleteFile($this->getLogFile());
    }

    /**
     * Return log file name
     *
     * @return string
     */
    public function getLogFile()
    {
        return LC_DIR_LOG . 'upgrade.log.' . date('Y-m-d') . '.php';
    }

    /**
     * Return last log file name
     *
     * @return string
     */
    public function getLastLogFile()
    {
        $result = null;

        $filter = new \Includes\Utils\FileFilter(
            LC_DIR_LOG,
            '/\/upgrade\.log\.\d{4}-\d{2}-\d{2}\.php$/',
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $paths = array();

        foreach ($filter->getIterator() as $file) {
            $paths[] = $file->getRealPath();
        }

        if ($paths) {
            arsort($paths);
            $result = reset($paths);
        }

        return $result;
    }

    /**
     * Return link to view the log file
     *
     * @return string
     */
    public function getLogURL()
    {
        return \XLite\Core\Converter::buildURL('upgrade', 'view_log_file');
    }

    /**
     * Add message to the log
     *
     * @param string  $message        Message text
     * @param array   $args           Arguments to substitute OPTIONAL
     * @param boolean $showTopMessage Flag OPTIONAL
     *
     * @return void
     */
    public function logInfo($message, array $args = array(), $showTopMessage = false)
    {
        $this->log($message, $args, $showTopMessage, \XLite\Core\TopMessage::INFO);
    }

    /**
     * Add message to the log
     *
     * @param string  $message        Message text
     * @param array   $args           Arguments to substitute OPTIONAL
     * @param boolean $showTopMessage Flag OPTIONAL
     *
     * @return void
     */
    public function logWarning($message, array $args = array(), $showTopMessage = false)
    {
        $this->log($message, $args, $showTopMessage, \XLite\Core\TopMessage::WARNING);
    }

    /**
     * Add message to the log
     *
     * @param string  $message        Message text
     * @param array   $args           Arguments to substitute OPTIONAL
     * @param boolean $showTopMessage Flag OPTIONAL
     *
     * @return void
     */
    public function logError($message, array $args = array(), $showTopMessage = false)
    {
        $this->log($message, $args, $showTopMessage, \XLite\Core\TopMessage::ERROR);
    }

    /**
     * Add message to the log
     *
     * @param string  $message        Message text
     * @param array   $args           Arguments to substitute OPTIONAL
     * @param boolean $showTopMessage Flag OPTIONAL
     * @param string  $topMessageType \XLite\Core\TopMessage class constant OPTIONAL
     *
     * @return void
     */
    protected function log($message, array $args = array(), $showTopMessage = false, $topMessageType = null)
    {
        // Write to file
        $this->write($this->getPrefix($topMessageType) . static::t($message, $args));

        // Show to admin
        if ($showTopMessage) {
            \XLite\Core\TopMessage::getInstance()->add($this->getTopMessage($message), $args, null, $topMessageType);
        }
    }

    /**
     * Write message to the file
     *
     * @param string $message Message text
     *
     * @return void
     */
    protected function write($message)
    {
        \Includes\Utils\FileManager::write($this->getLogFile(), $message . PHP_EOL, FILE_APPEND);
    }

    /**
     * Get message prefix
     *
     * @param string $type Prefix type
     *
     * @return string
     */
    protected function getPrefix($type)
    {
        return '[' . $type . ', ' . date('M d Y H:i:s') . '] ';
    }

    /**
     * Prepare message to display (not log)
     *
     * @param string $message Message text
     *
     * @return string
     */
    protected function getTopMessage($message)
    {
        return $message . '<p /><a target="_blank" href=' . $this->getLogURL() . '><u>' . static::t('See log file for details') . '</u></a>';
    }
}
