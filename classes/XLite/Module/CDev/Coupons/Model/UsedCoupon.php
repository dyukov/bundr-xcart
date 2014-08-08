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

namespace XLite\Module\CDev\Coupons\Model;

/**
 * Used coupon
 *
 * @Entity
 * @Table  (name="order_coupons")
 */
class UsedCoupon extends \XLite\Model\AEntity
{
    /**
     * Product unique ID
     *
     * @var   integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="uinteger")
     */
    protected $id;

    /**
     * Code
     *
     * @var   string
     *
     * @Column (type="fixedstring", length=16)
     */
    protected $code;

    /**
     * Value
     *
     * @var   float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $value = 0.0000;

    /**
     * Order
     *
     * @var   \XLite\Model\Order
     *
     * @ManyToOne  (targetEntity="XLite\Model\Order", inversedBy="usedCoupons")
     * @JoinColumn (name="order_id", referencedColumnName="order_id")
     */
    protected $order;

    /**
     * Coupon
     *
     * @var   \XLite\Module\CDev\Coupons\Model\Coupon
     *
     * @ManyToOne  (targetEntity="XLite\Module\CDev\Coupons\Model\Coupon", inversedBy="usedCoupons")
     * @JoinColumn (name="coupon_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $coupon;

    // {{{ Getters / setters

    /**
     * setCoupon
     *
     * @param \XLite\Module\CDev\Coupons\Model\Coupon $coupon ____param_comment____
     *
     * @return void
     */
    public function setCoupon(\XLite\Module\CDev\Coupons\Model\Coupon $coupon)
    {
        $this->coupon = $coupon;
        $this->setCode($coupon->getCode());
    }

    /**
     * Get public code (masked)
     *
     * @return string
     */
    public function getPublicCode()
    {
        return $this->getActualCode();
    }

    /**
     * Get actual code
     *
     * @return string
     */
    public function getActualCode()
    {
        return $this->getCoupon() ? $this->getCoupon()->getCode() : $this->getCode();
    }

    /**
     * Check - coupon deleted or not
     *
     * @return boolean
     */
    public function isDeleted()
    {
        return !(bool)$this->getCoupon();
    }

    // }}}

    // {{{ Processing

    /**
     * Mark as used
     *
     * @return void
     */
    public function markAsUsed()
    {
        if ($this->getCoupon()) {
            $this->getCoupon()->setUses($this->getCoupon()->getUses() + 1);
        }
    }

    /**
     * Unmark as used
     *
     * @return void
     */
    public function unmarkAsUsed()
    {
        if ($this->getCoupon() && 0 < $this->getCoupon()->getUses()) {
            $this->getCoupon()->setUses($this->getCoupon()->getUses() - 1);
        }
    }

    // }}}
}
