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
 * Coupon
 *
 * @Entity (repositoryClass="\XLite\Module\CDev\Coupons\Model\Repo\Coupon")
 * @Table  (name="coupons",
 *      indexes={
 *          @Index (name="ce", columns={"code", "enabled"})
 *      }
 * )
 */
class Coupon extends \XLite\Model\AEntity
{
    /**
     * Coupon types
     */
    const TYPE_PERCENT  = '%';
    const TYPE_ABSOLUTE = '$';

    /**
     * Coupon validation crror codes
     */
    const ERROR_DISABLED      = 'disabled';
    const ERROR_EXPIRED       = 'expired';
    const ERROR_USES          = 'uses';
    const ERROR_TOTAL         = 'total';
    const ERROR_PRODUCT_CLASS = 'product_class';
    const ERROR_MEMBERSHIP    = 'membership';


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
     * Enabled status
     *
     * @var   boolean
     *
     * @Column (type="boolean")
     */
    protected $enabled = true;

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
     * Comment
     *
     * @var   string
     *
     * @Column (type="string", length=64)
     */
    protected $comment = '';

    /**
     * Uses count
     *
     * @var   integer
     *
     * @Column (type="uinteger")
     */
    protected $uses = 0;

    /**
     * Date range (begin)
     *
     * @var   integer
     *
     * @Column (type="uinteger")
     */
    protected $dateRangeBegin = 0;

    /**
     * Date range (end)
     *
     * @var   integer
     *
     * @Column (type="uinteger")
     */
    protected $dateRangeEnd = 0;

    /**
     * Total range (begin)
     *
     * @var   float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $totalRangeBegin = 0;

    /**
     * Total range (end)
     *
     * @var   float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $totalRangeEnd = 0;

    /**
     * Uses limit
     *
     * @var   integer
     *
     * @Column (type="uinteger")
     */
    protected $usesLimit = 0;

    /**
     * Product classes
     *
     * @var   \Doctrine\Common\Collections\ArrayCollection
     *
     * @ManyToMany (targetEntity="XLite\Model\ProductClass", inversedBy="coupons")
     * @JoinTable (name="product_class_coupons",
     *      joinColumns={@JoinColumn(name="coupon_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="class_id", referencedColumnName="id")}
     * )
     */
    protected $productClasses;

    /**
     * Memberships
     *
     * @var   \Doctrine\Common\Collections\ArrayCollection
     *
     * @ManyToMany (targetEntity="XLite\Model\Membership", inversedBy="coupons")
     * @JoinTable (name="membership_coupons",
     *      joinColumns={@JoinColumn(name="coupon_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="membership_id", referencedColumnName="membership_id")}
     * )
     */
    protected $memberships;

    /**
     * Used coupons
     *
     * @var   \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Module\CDev\Coupons\Model\UsedCoupon", mappedBy="coupon")
     */
    protected $usedCoupons;

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->productClasses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->memberships    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usedCoupons    = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    // {{{ Logic

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
     * Check coupon activity
     *
     * @param \XLite\Model\Order $order Order OPTIONAL
     *
     * @return boolean
     */
    public function isActive(\XLite\Model\Order $order = null)
    {
        return 0 == count($this->getErrorCodes($order));
    }

    /**
     * Get coupon error codes
     *
     * @param \XLite\Model\Order $order Order OPTIONAL
     *
     * @return array
     */
    public function getErrorCodes(\XLite\Model\Order $order = null)
    {
        $result = array();

        if (!$this->getEnabled()) {
            $result[] = self::ERROR_DISABLED;
        }

        if (
            (0 < $this->getDateRangeBegin() && $this->getDateRangeBegin() > \XLite\Core\Converter::time())
            || $this->isExpired()
        ) {
            $result[] = self::ERROR_EXPIRED;
        }

        if (0 < $this->getUsesLimit() && $this->getUsesLimit() <= $this->getUses()) {
            $result[] = self::ERROR_USES;
        }

        if ($order) {

            // Check by order
            $result += $this->getOrderErrors($order);

            $result += $this->getMembershipErrors($order);

            $result += $this->getProductClassErrors($order);
        }

        return $result;
    }

    /**
     * Check - coupon is expired or not
     *
     * @return boolean
     */
    public function isExpired()
    {
        return 0 < $this->getDateRangeEnd() && $this->getDateRangeEnd() < \XLite\Core\Converter::time();
    }

    /**
     * Get amount
     *
     * @param \XLite\Model\Order $order Order
     *
     * @return float
     */
    public function getAmount(\XLite\Model\Order $order)
    {
        $total = $this->getOrderTotal($order);

        return $this->isAbsolute()
            ? min($total, $this->getValue())
            : ($total * $this->getValue() / 100);
    }

    /**
     * Get order total
     *
     * @param \XLite\Model\Order $order Order
     *
     * @return float
     */
    protected function getOrderTotal(\XLite\Model\Order $order)
    {
        if (0 == count($this->getProductClasses())) {

            $total = $order->getSubtotal();

        } else {

            $total = 0;

            foreach ($order->getItems() as $item) {
                if (
                    $item->getProduct()->getProductClass()
                    && $this->getProductClasses()->contains($item->getProduct()->getProductClass())
                ) {
                    $total += $item->getSubtotal();
                }
            }
        }

        return $total;
    }

    // }}}

    /**
     * Get the common order errors
     *
     * @param \XLite\Model\Order $order
     *
     * @return array
     */
    protected function getOrderErrors(\XLite\Model\Order $order)
    {
        $result = array();

        $total = $this->getOrderTotal($order);

        if (
            (0 < $this->getTotalRangeBegin() && $this->getTotalRangeBegin() > $total)
            || (0 < $this->getTotalRangeEnd() && $this->getTotalRangeEnd() < $total)
        ) {
            $result[] = self::ERROR_TOTAL;
        }

        return $result;
    }

    /**
     * Get the errors which are connected with the membership of the user who placed the order
     *
     * @param \XLite\Model\Order $order
     *
     * @return array
     */
    protected function getMembershipErrors(\XLite\Model\Order $order)
    {
        $found = true;

        $memberships = $this->getMemberships();

        // Memberhsip
        if (0 < count($memberships)) {

            $membership = $order->getProfile() ? $order->getProfile()->getMembership() : null;

            $found = $membership
                ? \Includes\Utils\ArrayManager::findValue(
                    $memberships,
                    array($this, 'checkMembershipId'),
                    $membership->getMembershipId()
                ) : false;
        }

        return $found ? array() : array(static::ERROR_MEMBERSHIP);
    }

    /**
     * Get the errors which are connected with the product classes of products in order
     *
     * @param \XLite\Model\Order $order
     *
     * @return array
     */
    protected function getProductClassErrors(\XLite\Model\Order $order)
    {
        $found = true;

        // Product classes
        if (0 < count($this->getProductClasses())) {

            $found = false;

            foreach ($order->getItems() as $item) {

                $found = $item->getProduct()->getProductClass()
                    && $this->getProductClasses()->contains($item->getProduct()->getProductClass());

                if ($found) {
                    break;
                }
            }
        }

        return $found ? array() : array(static::ERROR_PRODUCT_CLASS);
    }

    /**
     * Check membership item id equal
     *
     * @param \XLite\Model\Membership $item
     * @param type $membershipId
     *
     * @return boolean
     */
    public function checkMembershipId(\XLite\Model\Membership $item, $membershipId)
    {
        return $item->getMembershipId() === $membershipId;
    }
}
