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

namespace XLite\View;

/**
 * Shipping estimate box
 *
 * @ListChild (list="cart.panel.box", weight="10")
 */
class ShippingEstimateBox extends \XLite\View\AView
{
    /**
     * Modifier (cache)
     *
     * @var \XLite\Model\Order\Modifier
     */
    protected $modifier;


    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = 'form_field/select_country.js';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'shopping_cart/parts/box.estimator.tpl';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getModifier()
            && $this->getModifier()->canApply();
    }

    /**
     * Check - shipping estimate and method selected or not
     *
     * @return boolean
     */
    protected function isShippingEstimate()
    {
        return \XLite\Model\Shipping::getInstance()->getDestinationAddress($this->getModifier()->getModifier())
            && $this->getModifier()->getMethod();
    }

    /**
     * Get modifier
     *
     * @return \XLite\Model\Order\Modifier
     */
    protected function getModifier()
    {
        if (!isset($this->modifier)) {
            $this->modifier = $this->getCart()->getModifier(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, 'SHIPPING');
        }

        return $this->modifier;
    }

    /**
     * Get shipping cost
     *
     * @return float
     */
    protected function getShippingCost()
    {
        $cart = $this->getCart();
        $cost = $cart->getSurchargesSubtotal(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, false);

        return $this->formatPrice($cost, $cart->getCurrency());
    }

    /**
     * Get shipping estimate address
     *
     * @return string
     */
    protected function getEstimateAddress()
    {
        $string = '';

        $address = \XLite\Model\Shipping::getInstance()->getDestinationAddress($this->getModifier()->getModifier());

        if (is_array($address)) {

            $country = \XLite\Core\Database::getRepo('XLite\Model\Country')->find($address['country']);

            if ($address['state']) {
                $state = \XLite\Core\Database::getRepo('XLite\Model\State')->find($address['state']);

            } elseif ($this->getCart()->getProfile() && $this->getCart()->getProfile()->getShippingAddress()) {
                $state = $this->getCart()->getProfile()->getShippingAddress()->getState();
            }

            if (
                $state
                && $country
                && (
                    !$state->getCountry()
                    || $state->getCountry()->getCode() != $country->getCode()
                )
            ) {
                $state = \XLite\Core\Database::getRepo('XLite\Model\State')->getOtherState($address['custom_state']);
            }
        }

        if (isset($country)) {
            $string = $country->getCountry();
        }

        if ($state && $state->getState()) {
            $string .= ', ' . ($state->getCode() && 3 > strlen($state->getCode()) ? $state->getCode() : $state->getState());
        }

        $string .= ', ' . $address['zipcode'];

        $string = rtrim(rtrim($string), ',');

        return $string;
    }

}
