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

namespace XLite\View\Order\Details\Admin;

/**
 * Payment actions widget (capture, refund, void etc)
 */
class PaymentActions extends \XLite\View\AView
{
    /**
     *  Widget parameter names
     */
    const PARAM_ORDER         = 'order';
    const PARAM_UNITS_FILTER  = 'unitsFilter';


    protected $allowedTransactions = null;


    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/payment_actions.tpl';
    }

    /**
     * Register CSS files
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
     * Return widget directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'order/order';
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
            self::PARAM_ORDER        => new \XLite\Model\WidgetParam\Object('Order', null, false, 'XLite\Model\Order'),
            self::PARAM_UNITS_FILTER => new \XLite\Model\WidgetParam\Set('Units filter', array(), false),
        );
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getParam(self::PARAM_ORDER)
            && 0 < count($this->getTransactions());
    }

    // {{{ Content helpers

    /**
     * Get transactions
     *
     * @return array
     */
    protected function getTransactions()
    {
        return $this->getParam(self::PARAM_ORDER)->getPaymentTransactions();
    }

    /**
     * Get backend transactions
     *
     * @return array
     */
    protected function getBackendTransactions($transaction)
    {
        return $transaction->getBackendTransactions();
    }

    /**
     * Get transaction human-readable status
     *
     * @param \XLite\Model\Payment\Transaction $transaction Transaction
     *
     * @return string
     */
    protected function getTransactionStatus(\XLite\Model\Payment\Transaction $transaction)
    {
        return static::t($transaction->getReadableStatus());
    }

    /**
     * Get transaction additional data
     *
     * @param \XLite\Model\Payment\Transaction $transaction Transaction
     *
     * @return array
     */
    protected function getTransactionData(\XLite\Model\Payment\Transaction $transaction)
    {
        $list = array();

        foreach ($transaction->getData() as $cell) {
            if ($cell->getLabel()) {
                $list[] = $cell;
            }
        }

        return $list;
    }

    /**
     * Get list of allowed backend transactions
     *
     * @param \XLite\Model\Payment\Transaction $transaction Payment transaction
     *
     * @return array
     */
    protected function getTransactionUnits($transaction = null)
    {
        if (!isset($this->allowedTransactions) && isset($transaction)) {

            $processor = $transaction->getPaymentMethod()->getProcessor();

            if ($processor) {

                $this->allowedTransactions = $processor->getAllowedTransactions();

                foreach ($this->allowedTransactions as $k => $v) {
                    if (!$processor->isTransactionAllowed($transaction, $v) || !$this->isTransactionFiltered($v)) {
                        unset($this->allowedTransactions[$k]);
                    }
                }
            }
        }

        return $this->allowedTransactions;
    }

    /**
     * Returns true if transaction is in filter
     *
     * @param string $transactionType Type of backend transaction
     *
     * @return boolean
     */
    protected function isTransactionFiltered($transactionType)
    {
        $filter = $this->getParam(self::PARAM_UNITS_FILTER);

        return (empty($filter) || in_array($transactionType, $filter));
    }

    /**
     * Returns true if unit is last in the array (for unit separator displaying)
     *
     * @param integer $key Key of unit in the array
     *
     * @return boolean
     */
    protected function isLastUnit($key)
    {
        $keys = array_keys($this->getTransactionUnits());
        return array_pop($keys) == $key;
    }

    // }}}
}

