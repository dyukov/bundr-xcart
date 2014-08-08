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
 * Event task progress bar
 */
class EventTaskProgress extends \XLite\View\AView
{
    /**
     * Widget parameter names
     */
    const PARAM_EVENT             = 'event';
    const PARAM_TITLE             = 'title';
    const PARAM_BLOCKING_NOTE     = 'blockingNote';
    const PARAM_NON_BLOCKING_NOTE = 'nonBlockingNote';

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'event_task_progress/controller.js';

        return $list;
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'event_task_progress/style.css';

        return $list;
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_EVENT             => new \XLite\Model\WidgetParam\String('Event name', null),
            static::PARAM_TITLE             => new \XLite\Model\WidgetParam\String('Progress bar title', null),
            static::PARAM_BLOCKING_NOTE     => new \XLite\Model\WidgetParam\String('Blocking note', null),
            static::PARAM_NON_BLOCKING_NOTE => new \XLite\Model\WidgetParam\String('Non-blocking note', null),
        );
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {   
        return 'event_task_progress/body.tpl';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getTmpVar();
    }

    /**
     * Get temporary variable data
     * 
     * @return array
     */
    protected function getTmpVar()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getParam(static::PARAM_EVENT));
    }

    // {{{ Content helpers

    /**
     * Get event title 
     * 
     * @return string
     */
    protected function getEventTitle()
    {
        return $this->getParam(static::PARAM_TITLE);
    }

    /**
     * Get event name
     * 
     * @return string
     */
    protected function getEvent()
    {
        return $this->getParam(static::PARAM_EVENT);
    }

    /**
     * Get percent
     * 
     * @return integer
     */
    protected function getPercent()
    {
        $rec = $this->getTmpVar();

        return 0 < $rec['position'] ? min(100, floor($rec['position'] / $rec['length'] * 100)) : 0;
    }

    /**
     * Check - current event driver is blocking or not
     * 
     * @return boolean
     */
    protected function isBlockingDriver()
    {
        return \XLite\Core\EventTask::getInstance()->getDriver()->isBlocking();
    }

    /**
     * Get blocking note 
     * 
     * @return string
     */
    protected function getBlockingNote()
    {
        return $this->getParam(static::PARAM_BLOCKING_NOTE);
    }

    /**
     * Get non-blocking note
     *
     * @return string
     */
    protected function getNonBlockingNote()
    {
        return $this->getParam(static::PARAM_NON_BLOCKING_NOTE);
    }

    // }}}

}

