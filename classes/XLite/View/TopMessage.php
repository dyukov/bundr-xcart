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

namespace XLite\View;

/**
 * Top message
 *
 * @ListChild (list="layout.main", weight="100")
 */
class TopMessage extends \XLite\View\AView
{
    /**
     * Messages 
     * 
     * @var array
     */
    protected $messages;

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = $this->getDir() . '/style.css';

        return $list;
    }

    /**
     * Get a list of JS files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = $this->getDir() . '/controller.js';

        return $list;
    }

    /**
     * getDir
     *
     * @return string
     */
    protected function getDir()
    {
        return 'top_message';
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/body.tpl';
    }

    /**
     * Get top messages
     *
     * @return array
     */
    protected function getTopMessages()
    {
        if (!isset($this->messages)) {
            $this->messages = \XLite\Core\TopMessage::getInstance()->unloadPreviousMessages();
        }

        return $this->messages;
    }

    /**
     * Get message text
     *
     * @param array $data Message
     *
     * @return string
     */
    protected function getText(array $data)
    {
        return $data[\XLite\Core\TopMessage::FIELD_TEXT];
    }

    /**
     * Get message type
     *
     * @param array $data Message
     *
     * @return string
     */
    protected function getType(array $data)
    {
        return $data[\XLite\Core\TopMessage::FIELD_TYPE];
    }

    /**
     * Get message prefix
     *
     * @param array $data Message
     *
     * @return string|void
     */
    protected function getPrefix(array $data)
    {
        switch ($data[\XLite\Core\TopMessage::FIELD_TYPE]) {

            case \XLite\Core\TopMessage::ERROR:
                $prefix = 'Error';
                break;

            case \XLite\Core\TopMessage::WARNING:
                $prefix = 'Warning';
                break;

            default:
                // ...
        }

        return isset($prefix) ? (static::t($prefix) . ':') : '';
    }

    /**
     * Check id box is visible or not
     *
     * :TODO: check if it's really needed, or it's possible to use "isVisible()" instead
     *
     * @return boolean
     */
    protected function isHidden()
    {
        return !$this->getTopMessages();
    }

    /**
     * Get a specific path
     *
     * @return string
     */
    protected function getPath()
    {
        return \XLite\Core\TopMessage::getInstance()->getPath();
    }
}
