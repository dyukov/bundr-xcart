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

namespace XLite\Module\CDev\Wholesale\Model;

/**
 * Wholesale price model
 *
 * @Entity (repositoryClass="\XLite\Module\CDev\Wholesale\Model\Repo\WholesalePrice")
 * @Table  (name="wholesale_prices",
 *      indexes={
 *          @Index (name="range", columns={"product_id", "membership_id", "quantityRangeBegin", "quantityRangeEnd"})
 *      }
 * )
 */
class WholesalePrice extends \XLite\Model\AEntity
{
    /**
     * Wholesale price unique ID
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
     * @Column (
     *      type="money",
     *      options={
     *          @\XLite\Core\Doctrine\Annotation\Behavior (list={"taxable"}),
     *          @\XLite\Core\Doctrine\Annotation\Purpose (name="net", source="clear"),
     *          @\XLite\Core\Doctrine\Annotation\Purpose (name="display", source="net")
     *      }
     *  )
     */
    protected $price = 0.0000;

    /**
     * Quantity range (begin)
     *
     * @var   integer
     *
     * @Column (type="uinteger")
     */
    protected $quantityRangeBegin = 1;

    /**
     * Quantity range (end)
     *
     * @var   integer
     *
     * @Column (type="uinteger")
     */
    protected $quantityRangeEnd = 0;

    /**
     * Relation to a membership entity
     *
     * @var   \XLite\Model\Membership
     *
     * @ManyToOne (targetEntity="XLite\Model\Membership")
     * @JoinColumn (name="membership_id", referencedColumnName="membership_id")
     */
    protected $membership;

    /**
     * Relation to a product entity
     *
     * @var   \XLite\Model\Product
     *
     * @ManyToOne  (targetEntity="XLite\Model\Product", inversedBy="attachments")
     * @JoinColumn (name="product_id", referencedColumnName="product_id")
     */
    protected $product;


    /**
     * Get clear price (required for net and display prices calculation)
     *
     * @return float
     */
    public function getClearPrice()
    {
        return $this->getPrice();
    }

    /**
     * Get "SAVE" value (percent difference)
     *
     * @return integer
     */
    public function getSavePriceValue()
    {
        $membership = \XLite\Core\Auth::getInstance()->getProfile()
            ? \XLite\Core\Auth::getInstance()->getProfile()->getMembership()
            : null;
        $price = \XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\WholesalePrice')
            ->getPrice(
                $this->getProduct(),
                $this->getProduct()->getMinQuantity($membership),
                $membership
            );

        return is_null($price) ? 0 : max(0, intval(($price - $this->getPrice()) / $price * 100));
    }

    /**
     * Return true if this price is for 1 item of product and for all customers
     *
     * @return boolean
     */
    public function isDefaultPrice()
    {
        $result = false;

        if (1 == $this->getQuantityRangeBegin() && is_null($this->getMembership())) {
            $cnd = new \XLite\Core\CommonCell();
            $cnd->{\XLite\Module\CDev\Wholesale\Model\Repo\WholesalePrice::P_MEMBERSHIP} = null;
            $cnd->{\XLite\Module\CDev\Wholesale\Model\Repo\WholesalePrice::P_QTY} = 1;
            $cnd->{\XLite\Module\CDev\Wholesale\Model\Repo\WholesalePrice::P_PRODUCT} = $this->getProduct();
            $cnd->{\XLite\Module\CDev\Wholesale\Model\Repo\WholesalePrice::P_LIMIT} = array(0, 1);

            $prices = \XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\WholesalePrice')->search($cnd);

            if (!empty($prices)) {
                $firstPrice = $prices[0];
                $result = ($this->getId() === $firstPrice->getId());
            }
        }

        return $result;
    }
}
