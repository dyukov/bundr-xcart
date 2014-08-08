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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\View\Checkout;

/**
 * Shipping methods list
 *
 * @ListChild (list="checkout.shipping.selected.sub.payment", weight="300")
 */
class PaymentMethodsList extends \XLite\View\AView
{

    /**
     * Payed cart flag
     *
     * @var   boolean
     */
    protected $isPayedCart;

    /**
     * Get JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'checkout/steps/shipping/parts/paymentMethods.js';

        return $list;
    }

    /**
     * Return flag if the cart has been already payed
     *
     * @return boolean
     */
    protected function isPayedCart()
    {
        if (!isset($this->isPayedCart)) {

            $this->isPayedCart = $this->getCart()->isPayed();
        }

        return $this->isPayedCart;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'checkout/steps/shipping/parts/paymentMethods.tpl';
    }

    /**
     * Prepare payment method icon
     *
     * @param string $icon Icon local path
     *
     * @return string
     */
    protected function preparePaymentMethodIcon($icon)
    {
        return \XLite\Core\Layout::getInstance()->getResourceWebPath($icon, \XLite\Core\Layout::WEB_PATH_OUTPUT_URL);
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && !$this->isPayedCart();
    }

    /**
     * Return list of available payment methods
     *
     * @return array
     */
    protected function getPaymentMethods()
    {
        return $this->getCart()->getPaymentMethods();
    }

    /**
     * Check - payment method is selected or not
     *
     * @param \XLite\Model\Payment\Method $method Payment methods
     *
     * @return boolean
     */
    protected function isPaymentSelected(\XLite\Model\Payment\Method $method)
    {
        return $this->getCart()->getPaymentMethod() == $method;
    }

}
