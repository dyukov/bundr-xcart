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

namespace XLite\Module\CDev\VolumeDiscounts\Logic\Order\Modifier;

/**
 * Value discount modifier
 */
class Discount extends \XLite\Logic\Order\Modifier\Discount
{
    /**
     * Modifier code is the same as a base Discount - this will be aggregated to the single 'Discount' line in cart totals
     */
    const MODIFIER_CODE = 'DISCOUNT';


    /**
     * Modifier type (see \XLite\Model\Base\Surcharge)
     *
     * @var   string
     */
    protected $type = \XLite\Model\Base\Surcharge::TYPE_DISCOUNT;

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
            && $this->hasDiscount();
    }

    /**
     * Calculate
     *
     * @return void
     */
    public function calculate()
    {
        $discount = $this->getDiscount();

        if ($discount) {
    
            $total = $discount->getAmount($this->order);

            if ($total) {
                $total = min($total, $this->getOrder()->getSubtotal());
                $this->addOrderSurcharge($this->code, $total * -1, false);

                // Distribute discount value among the ordered products
                $this->distributeDiscount($total);
            }

        } else {
            $discount = null;
        }
    }

    /**
     * Check for suitable discount 
     * 
     * @return boolean
     */
    protected function hasDiscount()
    {
        $discount = $this->getDiscount();

        return !empty($discount) && 0 < $discount->getAmount($this->order);
    }

    /**
     * Get suitable discount from database
     * 
     * @return \XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount
     */
    protected function getDiscount()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount')
            ->getFirstDiscountBySubtotal(
                $this->getOrder()->getSubtotal(),
                $this->getOrder()->getProfile() ? $this->getOrder()->getProfile()->getMembership() : null
            );
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

        $info->name = \XLite\Core\Translation::lbl('Discount');

        return $info;
    }

    // }}}
}
