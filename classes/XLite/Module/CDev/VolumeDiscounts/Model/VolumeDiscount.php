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

namespace XLite\Module\CDev\VolumeDiscounts\Model;

/**
 * Volume discount model
 *
 * @Entity (repositoryClass="\XLite\Module\CDev\VolumeDiscounts\Model\Repo\VolumeDiscount")
 * @Table  (name="volume_discounts",
 *      indexes={
 *          @Index (name="range", columns={"subtotalRangeBegin", "subtotalRangeEnd"})
 *      }
 * )
 */
class VolumeDiscount extends \XLite\Model\AEntity
{
    const TYPE_PERCENT  = '%';
    const TYPE_ABSOLUTE = '$';


    /**
     * Discount unique ID
     *
     * @var   integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="uinteger")
     */
    protected $id;

    /**
     * Value
     *
     * @var   float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $value = 0.0000;

    /**
     * Type
     *
     * @var   string
     *
     * @Column (type="fixedstring", length=1)
     */
    protected $type = self::TYPE_PERCENT;

    /**
     * Subtotal range (begin)
     *
     * @var   float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $subtotalRangeBegin = 0;

    /**
     * Subtotal range (end)
     *
     * @var   float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $subtotalRangeEnd = 0;

    /**
     * Membership
     *
     * @var   \XLite\Model\Membership
     *
     * @ManyToOne (targetEntity="XLite\Model\Membership")
     * @JoinColumn (name="membership_id", referencedColumnName="membership_id")
     */
    protected $membership;


    /**
     * Check - discount is absolute or not
     *
     * @return boolean
     */
    public function isAbsolute()
    {
        return static::TYPE_ABSOLUTE == $this->getType();
    }

    /**
     * Get discount amount
     *
     * @param \XLite\Model\Order $order Order
     *
     * @return float
     */
    public function getAmount(\XLite\Model\Order $order)
    {
        $discount = $this->isAbsolute()
            ? $this->getValue()
            : ($order->getSubtotal() * $this->getValue() / 100);

        return min($discount, $order->getSubtotal());
    }

    /**
     * Get fingerprint 
     * 
     * @return string
     */
    public function getFingerprint()
    {
        return $this->getSubtotalRangeBegin() . ':'
            . ($this->getMembership() ? $this->getMembership()->getMembershipId() : 0);
    }
}
