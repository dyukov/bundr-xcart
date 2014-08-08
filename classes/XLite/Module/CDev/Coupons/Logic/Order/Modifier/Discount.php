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

namespace XLite\Module\CDev\Coupons\Logic\Order\Modifier;

/**
 * Discount coupons modifier
 */
class Discount extends \XLite\Logic\Order\Modifier\Discount
{
    const MODIFIER_CODE = 'DCOUPON';

    /**
     * Modifier unique code
     *
     * @var   string
     */
    protected $code = self::MODIFIER_CODE;

    // {{{ Calculation

    /**
     * Check - can apply this modifier or not
     *
     * @return boolean
     */
    public function canApply()
    {
        return parent::canApply()
            && $this->checkCoupons();
    }

    /**
     * Calculate
     *
     * @return void
     */
    public function calculate()
    {
        $total = 0;

        foreach ($this->getUsedCoupons() as $used) {
            $used->setValue($used->getCoupon()->getAmount($this->order));
            $total += $used->getValue();
        }

        if ($total) {
            $total = min($total, $this->order->getSubtotal());
            $this->addOrderSurcharge($this->code, $total * -1, false);

            // Distribute discount value among the ordered products
            $this->distributeDiscount($total);
        }

    }

    /**
     * Check coupons
     *
     * @return boolean
     */
    protected function checkCoupons()
    {
        if ($this->order instanceOf \XLite\Model\Cart) {
            foreach ($this->getUsedCoupons() as $used) {
                if (
                    !$used->getCoupon()
                    || !$used->getCoupon()->isActive($this->order)
                ) {
                    $this->getUsedCoupons()->removeElement($used);
                    \XLite\Core\Database::getEM()->remove($used);
                }
            }
        }

        return 0 < count($this->getUsedCoupons());
    }

    /**
     * Get used coupons
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function getUsedCoupons()
    {
        return $this->order->getUsedCoupons();
    }

    // }}}

    // {{{ Content helpers

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
        $info->name = static::t('Coupon discount');

        return $info;
    }

    // }}}
}
