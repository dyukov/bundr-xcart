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
 * Shipping estimator
 *
 * @ListChild (list="center")
 */
class ShippingEstimate extends \XLite\View\AView
{
    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();

        $result[] = 'shipping_estimate';

        return $result;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'shopping_cart/shipping_estimator/body.tpl';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getModifier();
    }

    /**
     * Get countries list
     *
     * @return array(\XLite\Model\Country)
     */
    protected function getCountries()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Country')
            ->findByEnabled(true);
    }

    /**
     * Check if the enabled address field with the given name exists
     *
     * @param string $fieldName Field name
     *
     * @return boolean
     */
    protected function hasField($fieldName)
    {
        return (bool) \XLite\Core\Database::getRepo('XLite\Model\AddressField')->findOneBy(
            array(
                'serviceName' => $fieldName,
                'enabled'     => true,
            )
        );
    }

    /**
     * Get selected country code
     *
     * @return string
     */
    protected function getCountryCode()
    {
        $address = $this->getAddress();

        $c = 'US';

        if ($address && isset($address['country'])) {
            $c = $address['country'];

        } elseif (\XLite\Core\Config::getInstance()->Shipping->anonymous_country) {
            $c = \XLite\Core\Config::getInstance()->Shipping->anonymous_country;
        }

        return $c;
    }

    /**
     * Get state
     *
     * @return \XLite\Model\State
     */
    protected function getState()
    {
        $address = $this->getAddress();

        $state = null;

        // From getDestinationAddress()
        if ($address && isset($address['state']) && $address['state']) {
            $state = \XLite\Core\Database::getRepo('XLite\Model\State')->find($address['state']);

        } elseif (
            $this->getCart()->getProfile()
            && $this->getCart()->getProfile()->getShippingAddress()
            && $this->getCart()->getProfile()->getShippingAddress()->getState()
        ) {

            // From shipping address
            $state = $this->getCart()->getProfile()->getShippingAddress()->getState();

        } elseif (
            !$address
            && \XLite\Core\Config::getInstance()->Shipping->anonymous_custom_state
        ) {

            // From config
            $state = new \XLite\Model\State();
            $state->setState(\XLite\Core\Config::getInstance()->Shipping->anonymous_custom_state);

        }

        return $state;
    }

    /**
     * Get state
     *
     * @return \XLite\Model\State
     */
    protected function getOtherState()
    {
        $state = null;

        $address = $this->getAddress();

        if (isset($address) && isset($address['custom_state'])) {
            $state = $address['custom_state'];

        } elseif (
            $this->getCart()->getProfile()
            && $this->getCart()->getProfile()->getShippingAddress()
            && $this->getCart()->getProfile()->getShippingAddress()->getiCustomState()
        ) {
            // From shipping address
            $state = $this->getCart()->getProfile()->getShippingAddress()->getCustomState();
        }

        return $state;
    }

    /**
     * Get ZIP code
     *
     * @return string
     */
    protected function getZipcode()
    {
        $address = $this->getAddress();

        return ($address && isset($address['zipcode']))
            ? $address['zipcode']
            : '';
    }

    /**
     * Check - shipping is estimate or not
     *
     * @return boolean
     */
    protected function isEstimate()
    {
        return $this->getAddress()
            && $this->getCart()->getProfile()
            && $this->getCart()->getProfile()->getShippingAddress();
    }

}
