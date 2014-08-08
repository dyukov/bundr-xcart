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

namespace XLite\Module\CDev\Coupons\Controller\Customer;

/**
 * Coupon controller
 */
class Coupon extends \XLite\Controller\Customer\ACustomer
{

    /**
     * Controller marks the cart calculation.
     * On the checkout page we need cart recalculation
     *
     * @return boolean
     */
    protected function markCartCalculate()
    {
        return true;
    }

    /**
     * Apply coupon to the cart
     *
     * @return void
     */
    protected function doActionAdd()
    {
        $code = strval(\XLite\Core\Request::getInstance()->code);
        $coupon = \XLite\Core\Database::getRepo('XLite\Module\CDev\Coupons\Model\Coupon')
            ->findOneBy(array('code' => $code));
        $codes = $coupon ? $coupon->getErrorCodes($this->getCart()) : array();

        $error = null;

        if (!$coupon || $codes) {
            $this->valid = false;

            if (
                $coupon
                && in_array(\XLite\Module\CDev\Coupons\Model\Coupon::ERROR_TOTAL, $codes)
            ) {
                $currency = $this->getCart()->getCurrency();

                if (
                    0 < $coupon->getTotalRangeBegin()
                    && 0 < $coupon->getTotalRangeEnd()
                ) {
                    $error = static::t(
                        'To use the coupon, your order subtotal must be between X and Y',
                        array(
                            'min' => $currency->formatValue($coupon->getTotalRangeBegin()),
                            'max' => $currency->formatValue($coupon->getTotalRangeEnd()),
                        )
                    );
                } elseif (0 < $coupon->getTotalRangeBegin()) {
                    $error = static::t(
                        'To use the coupon, your order subtotal must be at least X',
                        array('min' => $currency->formatValue($coupon->getTotalRangeBegin()))
                    );

                } else {
                    $error = static::t(
                        'To use the coupon, your order subtotal must not exceed Y',
                        array('max' => $currency->formatValue($coupon->getTotalRangeEnd()))
                    );
                }
            } else {
                $error = static::t(
                    'There is no such a coupon, please check the spelling: X',
                    array('code' => $code)
                );
            }

        } else {
            $found = false;
            foreach ($this->getCart()->getUsedCoupons() as $usedCoupon) {
                if (
                    $usedCoupon->getCoupon()
                    && $usedCoupon->getCoupon()->getId() == $coupon->getId()
                ) {
                    $found = true;
                    break;
                }
            }

            if ($found) {

                // Duplicate
                $this->valid = false;
                $error = static::t('You have already used the coupon');

            } else {

                // Create
                $usedCoupon = new \XLite\Module\CDev\Coupons\Model\UsedCoupon;
                $usedCoupon->setOrder($this->getCart());
                $this->getCart()->addUsedCoupons($usedCoupon);
                $usedCoupon->setCoupon($coupon);
                $coupon->addUsedCoupons($usedCoupon);
                \XLite\Core\Database::getEM()->persist($usedCoupon);

                $this->updateCart();

                \XLite\Core\Database::getEM()->flush();

                \XLite\Core\TopMessage::addInfo('The coupon has been applied to your order');
            }
        }

        if ($error) {
            \XLite\Core\Event::invalidElement('code', $error);
        }

        $this->setPureAction();
    }

    /**
     * Remove coupon from the cart
     *
     * @return void
     */
    protected function doActionRemove()
    {
        $id = \XLite\Core\Request::getInstance()->id;

        $found = null;
        foreach ($this->getCart()->getUsedCoupons() as $usedCoupon) {
            if ($usedCoupon->getId() == $id) {
                $found = $usedCoupon;
                break;
            }
        }

        if ($found) {
            $this->getCart()->getUsedCoupons()->removeElement($found);
            \XLite\Core\Database::getEM()->remove($found);

            $this->updateCart();

            \XLite\Core\Database::getEM()->flush();

        } else {
            // Not found
            $this->valid = false;
        }

        $this->setPureAction();
    }
}
