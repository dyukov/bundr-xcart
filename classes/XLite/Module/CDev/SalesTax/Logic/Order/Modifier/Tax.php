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

namespace XLite\Module\CDev\SalesTax\Logic\Order\Modifier;

/**
 * Tax  business logic
 */
class Tax extends \XLite\Logic\Order\Modifier\ATax
{
    /**
     * Modifier unique code
     *
     * @var string
     */
    protected $code = 'CDEV.STAX';

    /**
     * Surcharge identification pattern
     *
     * @var string
     */
    protected $identificationPattern = '/^CDEV\.STAX\.\d+$/Ss';


    /**
     * Check - can apply this modifier or not
     *
     * @return boolean
     */
    public function canApply()
    {
        return parent::canApply()
            && $this->getTaxes();
    }

    // {{{ Calculation

    /**
     * Calculate
     *
     * @return void
     */
    public function calculate()
    {
        $zones = $this->getZonesList();
        $membership = $this->getMembership();

        foreach ($this->getTaxes() as $tax) {
            $previousItems = array();
            $previousClasses = array();
            $cost = 0;
            $ratesExists = false;

            foreach ($tax->getFilteredRates($zones, $membership) as $rate) {
                $ratesExists = true;
                $taxClass = $rate->getTaxClass() ?: null;
                if (!in_array($taxClass, $previousClasses)) {

                    // Get tax cost for products in the cart with specified product class
                    $items = $this->getTaxableItems($rate, $previousItems);
                    if ($items) {
                        foreach ($items as $item) {
                            $previousItems[] = $item->getProduct()->getProductId();
                        }
                        $cost += $rate->calculate($items);
                    }

                    // Add shipping tax cost
                    $cost += $rate->calculateShippingTax($this->getTaxableShippingCost($taxClass));

                    $previousClasses[] = $taxClass;
                }
            }

            if ($cost) {
                $this->addOrderSurcharge(
                    $this->code . '.' . $tax->getId(),
                    doubleval($cost),
                    false,
                    $ratesExists
                );
            }
        }
    }

    /**
     * Get taxable shipping cost
     *
     * @param \XLite\Model\TaxClass $class Product class object
     *
     * @return float
     */
    protected function getTaxableShippingCost($class)
    {
        $result = 0;

        $modifier = $this->order->getModifier(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, 'SHIPPING');

        if ($modifier && $modifier->getSelectedRate() && $modifier->getSelectedRate()->getMethod()) {

            $rate = $modifier->getSelectedRate();

            if (
                !$class
                || (
                    $class
                    && $rate->getMethod()->getTaxClass()
                    && $rate->getMethod()->getTaxClass()->getId() == $class->getId()
                )
            ) {
                $result = $rate->getTaxableBasis();
            }
        }

        return $result;
    }

    /**
     * Get taxes
     *
     * @return array
     */
    protected function getTaxes()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\CDev\SalesTax\Model\Tax')->findActive();
    }

    /**
     * Get zones list
     *
     * @return array
     */
    protected function getZonesList()
    {
        $address = $this->getAddress();

        $zones = $address ? \XLite\Core\Database::getRepo('XLite\Model\Zone')->findApplicableZones($address) : array();

        foreach ($zones as $i => $zone) {
            $zones[$i] = $zone->getZoneId();
        }

        return $zones;
    }

    /**
     * Get membership
     *
     * @return \XLite\Model\Membership
     */
    protected function getMembership()
    {
        return $this->getOrder()->getProfile()
            ? $this->getOrder()->getProfile()->getMembership()
            : null;
    }

    /**
     * Get taxable items 
     * 
     * @param \XLite\Module\CDev\SalesTax\Model\Tax\Rate $rate
     * @param array                                      $previousItems Previous selected items OPTIONAL
     *  
     * @return array
     */
    protected function getTaxableItems(\XLite\Module\CDev\SalesTax\Model\Tax\Rate $rate, array $previousItems = array())
    {
        $list = array();

        foreach ($this->getOrder()->getItems() as $item) {
            if (
                $item->getProduct()->getTaxable()
                && !in_array($item->getProduct()->getProductId(), $previousItems)
                && (
                    (
                        $rate->getNoTaxClass()
                        && !$item->getProduct()->getTaxClass()
                    )
                    || (
                        !$rate->getNoTaxClass()
                        && (
                            !$rate->getTaxClass()
                            || (
                                $item->getProduct()->getTaxClass()
                                && $item->getProduct()->getTaxClass()->getId() == $rate->getTaxClass()->getId()
                            )
                        )
                    )
                ) 
            ) {
                $list[] = $item;
            }
        }

        return $list;
    }

    /**
     * Get address for zone calculator
     *
     * @return array
     */
    protected function getAddress()
    {
        $address = null;
        $addressObj = $this->getOrderAddress();
        if ($addressObj) {
            // Profile is exists
            $address = \XLite\Model\Shipping::prepareAddressData($addressObj);
        }

        return $address;
    }

    /**
     * Get order-based address
     *
     * @return \XLite\Model\Address
     */
    protected function getOrderAddress()
    {
        return ($this->getOrder()->getProfile() && $this->getOrder()->getProfile()->getBillingAddress())
            ? $this->getOrder()->getProfile()->getBillingAddress()
            : null;
    }

    // }}}

    // {{{ Surcharge operations

    /**
     * Get surcharge name
     *
     * @param \XLite\Model\Order\Surcharge $surcharge Surcharge
     *
     * @return \XLite\DataSet\Transport\Order\Surcharge
     */
    public function getSurchargeInfo(\XLite\Model\Base\Surcharge $surcharge)
    {
        $info = new \XLite\DataSet\Transport\Order\Surcharge;

        if (0 === strpos($surcharge->getCode(), $this->code . '.')) {
            $id = intval(substr($surcharge->getCode(), strlen($this->code) + 1));
            $tax = \XLite\Core\Database::getRepo('XLite\Module\CDev\SalesTax\Model\Tax')->find($id);
            $info->name = $tax
                ? $tax->getName()
                : \XLite\Core\Translation::lbl('Sales tax');

        } else {
            $info->name = \XLite\Core\Translation::lbl('Sales tax');
        }

        $info->notAvailableReason = \XLite\Core\Translation::lbl('Billing address is not defined');

        return $info;
    }

    // }}}
}
