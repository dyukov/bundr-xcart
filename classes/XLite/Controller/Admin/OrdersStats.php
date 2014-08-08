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

namespace XLite\Controller\Admin;

/**
 * Orders statistics page controller
 */
class OrdersStats extends \XLite\Controller\Admin\Stats
{
    /**
     * Columns
     */
    const P_PROCESSED  = 'processed';
    const P_QUEUED     = 'queued';
    const P_CANCELED   = 'canceled';
    const P_DECLINED   = 'declined';
    const P_TOTAL      = 'total';
    const P_PAID       = 'paid';

    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    public function checkACL()
    {
        return parent::checkACL() || \XLite\Core\Auth::getInstance()->isPermissionAllowed('manage orders');
    }

    /**
     * getPageTemplate
     *
     * @return void
     */
    public function getPageTemplate()
    {
        return 'orders_stats.tpl';
    }

    /**
     * Get row headings
     *
     * @return array
     */
    public function getRowTitles()
    {
        return array(
            self::P_PROCESSED  => 'Processed/Completed',
            self::P_QUEUED     => 'Queued',
            self::P_DECLINED   => 'Declined',
            self::P_CANCELED   => 'Canceled',
            self::P_TOTAL      => 'Total',
            self::P_PAID       => 'Paid',
        );
    }

    /**
     * Status rows as row identificator => included statuses
     *
     * @return array
     */
    public function getStatusRows()
    {
        return array(
            static::P_PROCESSED => array(
                \XLite\Model\Order\Status\Payment::STATUS_AUTHORIZED,
                \XLite\Model\Order\Status\Payment::STATUS_PAID,
                \XLite\Model\Order\Status\Payment::STATUS_PART_PAID,
            ),
            static::P_QUEUED => array(
                \XLite\Model\Order\Status\Payment::STATUS_QUEUED,
            ),
            static::P_DECLINED => array(
                \XLite\Model\Order\Status\Payment::STATUS_DECLINED,
            ),
            static::P_CANCELED => array(
                \XLite\Model\Order\Status\Payment::STATUS_CANCELED,
            ),
            static::P_TOTAL => array(
                \XLite\Model\Order\Status\Payment::STATUS_DECLINED,
                \XLite\Model\Order\Status\Payment::STATUS_QUEUED,
                \XLite\Model\Order\Status\Payment::STATUS_AUTHORIZED,
                \XLite\Model\Order\Status\Payment::STATUS_PAID,
                \XLite\Model\Order\Status\Payment::STATUS_PART_PAID,
            ),
            static::P_PAID => array(
                \XLite\Model\Order\Status\Payment::STATUS_AUTHORIZED,
                \XLite\Model\Order\Status\Payment::STATUS_PAID,
                \XLite\Model\Order\Status\Payment::STATUS_PART_PAID,
            ),
        );
    }

    /**
     * Is totals row
     *
     * @param string $row Row identificator
     *
     * @return boolean
     */
    public function isTotalsRow($row)
    {
        return in_array(
            $row,
            array(
                self::P_PAID,
                self::P_TOTAL,
            )
        );
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getStatsRows()
    {
        return array_keys($this->getStatusRows());
    }

    /**
     * Prepare statistics table
     *
     * @return array
     */
    public function getStats()
    {
        if (is_null($this->stats)) {
            $this->stats = $this->initStats();
            array_map(array($this, 'processStatsRecord'), $this->getData());
        }

        return $this->stats;
    }

    /**
     * Get data
     *
     * @return array
     */
    protected function getData()
    {
        $cnd = $this->getSearchCondition();
        return \XLite\Core\Database::getRepo('\XLite\Model\Order')->search($cnd);
    }

    /**
     * Collect statistics record
     *
     * @param string             $row   Row identificator
     * @param \XLite\Model\Order $order Order
     *
     * @return void
     */
    protected function collectStatsRecord($row, $order)
    {
        foreach ($this->getStatsColumns() as $period) {
            if ($order->getDate() >= $this->getStartTime($period)) {
                if ($this->isTotalsRow($row)) {
                    $this->stats[$row][$period] += $order->getTotal();
                } else {
                    $this->stats[$row][$period] += 1;
                }
            }
        }
    }

    /**
     * Process statistics record
     *
     * @param \XLite\Model\Order $order Order
     *
     * @return void
     */
    protected function processStatsRecord($order)
    {
        foreach ($this->getStatusRows() as $row => $includedStatuses) {
            if (in_array($order->getPaymentStatusCode(), $includedStatuses)) {
                $this->collectStatsRecord($row, $order);
            }
        }
    }
}
