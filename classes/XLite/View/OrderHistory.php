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
 * Order history widget
 *
 * @ListChild (list="order.operations", weight="150", zone="admin")
 */
class OrderHistory extends \XLite\View\AView
{
    /**
     * Widget parameters
     */
    const PARAM_ORDER = 'order';

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrderId()
    {
        return $this->getOrder()->getOrderId();
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'order/history/style.css';

        return $list;
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'order/history/script.js';

        return $list;
    }

    /**
     * Return default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'order/history/body.tpl';
    }

    /**
     * Check widget visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getOrderId();
    }

    /**
     * Get blocks for the events of order
     *
     * @return array
     */
    protected function getOrderHistoryEventsBlock()
    {
        $result = array();

        $list = \XLite\Core\Database::getRepo('XLite\Model\OrderHistoryEvents')
            ->search($this->getOrderId());
        foreach ($list as $event) {
            $result[$this->getDayDate($event->getDate())][] = $event;
        }

        return $result;
    }

    /**
     * Return true if event has comment or details
     * 
     * @param \XLite\Model\OrderHistoryEvents $event Event object
     *  
     * @return boolean
     */
    protected function isDisplayDetails(\XLite\Model\OrderHistoryEvents $event)
    {
        return $event->getComment() || $this->getDetails($event);
    }

    /**
     * Date getter
     *
     * @param \XLite\Model\OrderHistoryEvents $event Event
     *
     * @return string
     */
    protected function getDate(\XLite\Model\OrderHistoryEvents $event)
    {
        return \XLite\Core\Converter::formatDayTime($event->getDate());
    }

    /**
     * Description getter
     *
     * @param \XLite\Model\OrderHistoryEvents $event Event
     *
     * @return string
     */
    protected function getDescription(\XLite\Model\OrderHistoryEvents $event)
    {
        return $event->getDescription();
    }

    /**
     * Comment getter
     *
     * @param \XLite\Model\OrderHistoryEvents $event Event
     *
     * @return string
     */
    protected function getComment(\XLite\Model\OrderHistoryEvents $event)
    {
        return $event->getComment();
    }

    /**
     * Details getter
     *
     * @param \XLite\Model\OrderHistoryEvents $event Event
     *
     * @return array
     */
    protected function getDetails(\XLite\Model\OrderHistoryEvents $event)
    {
        $list = array();

        $columnId = 0;

        foreach ($event->getDetails() as $cell) {
            if ($cell->getName()) {
                $list[$columnId][] = $cell;
                $columnId++;
            }

            if ($this->getColumnsNumber() <= $columnId) {
                $columnId = 0;
            }
        }

        return $list;
    }

    /**
     * Get number of columns to display event details 
     * 
     * @return integer
     */
    protected function getColumnsNumber()
    {
        return 3;
    }

    /**
     * Get day of the given date
     *
     * @param integer $date Date (UNIX timestamp)
     *
     * @return string
     */
    protected function getDayDate($date)
    {
        return \XLite\Core\Converter::formatDate($date);
    }

    /**
     * Return header of the block
     *
     * @param string $index Index
     *
     * @return string
     */
    protected function getHeaderBlock($index)
    {
        return $index;
    }
}
