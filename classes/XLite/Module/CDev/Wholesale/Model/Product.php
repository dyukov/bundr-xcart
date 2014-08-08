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
 * Product
 */
class Product extends \XLite\Model\Product implements \XLite\Base\IDecorator
{
    /**
     * Storage of current wholesale quantity according to the clear price will be calculated
     *
     * @var integer
     */
    protected $wholesaleQuantity = 0;

    /**
     * Wholesale quantity setter
     *
     * @param integer $value
     *
     * @return void
     */
    public function setWholesaleQuantity($value)
    {
        $this->wholesaleQuantity = $value;
    }

    /**
     * Wholesale quantity getter
     *
     * @return integer
     */
    public function getWholesaleQuantity()
    {
        return $this->wholesaleQuantity;
    }

    /**
     * Get minimum product quantity available to customer to purchase
     *
     * @param \XLite\Model\Membership $membership Customer's membership OPTIONAL
     *
     * @return integer
     */
    public function getMinQuantity($membership = null)
    {
        $minQuantity = \XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\MinQuantity')
            ->getMinQuantity(
                $this,
                $membership
            );

        return isset($minQuantity) ? $minQuantity->getQuantity() : 1;
    }

    /**
     * Check if wholesale prices are enabled for the specified product.
     * Return true if product is not on sale (Sale module)
     *
     * @return boolean
     */
    public function isWholesalePricesEnabled()
    {
        return !\XLite\Core\Operator::isClassExists('\XLite\Module\CDev\Sale\Main')
            || !$this->getParticipateSale();
    }

    /**
     * Override clear price of product
     *
     * @return float
     */
    public function getClearPrice()
    {
        $price = parent::getClearPrice();

        if ($this->isWholesalePricesEnabled()) {

            $membership = \XLite\Core\Auth::getInstance()->getProfile()
                ? \XLite\Core\Auth::getInstance()->getProfile()->getMembership()
                : null;

            $wholesalePrice = \XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\WholesalePrice')->getPrice(
                $this,
                $this->getWholesaleQuantity() ?: $this->getMinQuantity($membership),
                $membership
            );

            if (!is_null($wholesalePrice)) {
                $price = $wholesalePrice;
            }
        }

        return $price;
    }

    /**
     * Clone
     *
     * @return \XLite\Model\AEntity
     */
    public function cloneEntity()
    {
        $newProduct = parent::cloneEntity();

        $this->cloneQuantity($newProduct);

        $this->cloneMembership($newProduct);

        return $newProduct;
    }

    /**
     * Clone quantity (used in cloneEntity() method)
     *
     * @param \XLite\Model\Product $newProduct
     *
     * @return void
     */
    protected function cloneQuantity($newProduct)
    {
        foreach (\XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\MinQuantity')->findBy(array('product' => $this)) as $quantity) {
            $newQuantity = $quantity->cloneEntity();
            $newQuantity->setProduct($newProduct);
            $newQuantity->setMembership($quantity->getMembership());
            $newQuantity->update();
        }
    }

    /**
     * Clone membership (used in cloneEntity() method)
     *
     * @param \XLite\Model\Product $newProduct
     *
     * @return void
     */
    protected function cloneMembership($newProduct)
    {
        foreach (\XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\WholesalePrice')->findBy(array('product' => $this)) as $price) {
            $newPrice = $price->cloneEntity();
            $newPrice->setProduct($newProduct);
            $newPrice->setMembership($price->getMembership());
            $newPrice->update();
        }
    }
}
