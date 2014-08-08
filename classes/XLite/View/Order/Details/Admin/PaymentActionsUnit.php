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
 * Payment actions unit widget (button capture or refund or void etc)
 *
 * @ListChild (list="order.details.payment_actions", zone="admin")
 */
class PaymentActionsUnit extends \XLite\View\AView
{
    /**
     *  Widget parameter names
     */
    const PARAM_TRANSACTION = 'transaction';
    const PARAM_UNIT        = 'unit';
    const PARAM_DISPLAY_SEPARATOR = 'displaySeparator';


    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'order/order/payment_actions/unit.tpl';
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
            self::PARAM_TRANSACTION => new \XLite\Model\WidgetParam\Object('Transaction', null, false, 'XLite\Model\Payment\Transaction'),
            self::PARAM_UNIT        => new \XLite\Model\WidgetParam\String('Unit', '', false),
            self::PARAM_DISPLAY_SEPARATOR => new \XLite\Model\WidgetParam\Bool('Display separator', false, false),
        );
    }

    /**
     * Get CSS class
     *
     * @return string
     */
    protected function getCSSClass()
    {
        return 'action payment-action-button';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getParam(self::PARAM_TRANSACTION)
            && $this->isTransactionUnitAllowed(
                $this->getParam(self::PARAM_TRANSACTION),
                $this->getParam(self::PARAM_UNIT)
            );
    }

    /**
     * Return true if requested unit is allowed for the transaction
     *
     * @return boolean
     */
    protected function isTransactionUnitAllowed($transaction, $unit)
    {
        return $transaction->getPaymentMethod()->getProcessor()->isTransactionAllowed($transaction, $unit);
    }

    /**
     * Return true if separator should be displayed after the button
     *
     * @return boolean
     */
    protected function isDisplaySeparator()
    {
        return $this->getParam(self::PARAM_DISPLAY_SEPARATOR);
    }

    /**
     * Get unit name (for button naming)
     *
     * @return string
     */
    protected function getUnitName()
    {
        return ucfirst($this->getParam(self::PARAM_UNIT));
    }

    /**
     * Get action URL
     *
     * @return string
     */
    protected function getActionURL()
    {
        return $this->buildURL(
            'order',
            $this->getParam(self::PARAM_UNIT),
            array(
                'order_number' => $this->getParam(self::PARAM_TRANSACTION)->getOrder()->getOrderNumber(),
                'trn_id'       => $this->getParam(self::PARAM_TRANSACTION)->getTransactionId(),
            )
        );
    }
}

