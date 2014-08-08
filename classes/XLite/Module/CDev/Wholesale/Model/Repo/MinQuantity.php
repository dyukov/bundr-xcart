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

namespace XLite\Module\CDev\Wholesale\Model\Repo;

/**
 * MinQuantity model repository
 */
class MinQuantity extends \XLite\Model\Repo\ARepo
{
    /**
     * Get minimum quantities for every membeship
     *
     * @param \XLite\Model\Product $product Product entity
     *
     * @return array
     */
    public function getAllMinQuantities(\XLite\Model\Product $product)
    {
        $result = array();

        $data = $this->getMinQuantity($product);

        $result[] = array(
            'name'          => 'All customers',
            'membershipId'  => 0,
            'quantity'      => $data ? $data->getQuantity() : 1,
        );

        foreach (\XLite\Core\Database::getRepo('XLite\Model\Membership')->findAll() as $membership) {

            $data = $this->getMinQuantity($product, $membership);

            $result[] = array(
                'name'          => $membership->getName(),
                'membershipId'  => $membership->getMembershipId(),
                'quantity'      => $data ? $data->getQuantity() : 1,
            );
        }

        return $result;
    }

    /**
     * Remove minimum quantity information for a given product.
     *
     * @param \XLite\Model\Product $product Product object to remove
     *
     * @return void
     */
    public function deleteByProduct(\XLite\Model\Product $product)
    {
        $this->defineDeleteByProductQuery($product)->execute();

        $this->flushChanges();
    }

    /**
     * Get minimum quantities for specified product and membership
     *
     * @param \XLite\Model\Product    $product    Product entity
     * @param \XLite\Model\Membership $membership Membership entity (or null) OPTIONAL
     *
     * @return \XLite\Module\CDev\Wholesale\Model\MinQuantity
     */
    public function getMinQuantity(\XLite\Model\Product $product, $membership = null)
    {
        return $this->defineMinQuantitiesQuery($product, $membership)->setMaxResults(1)->getSingleResult();
    }

    /**
     * Define query builder for getMinQuantities()
     *
     * @param \XLite\Model\Product    $product    Product entity
     * @param \XLite\Model\Membership $membership Membership entity (or null) OPTIONAL
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineMinQuantitiesQuery($product, $membership = null)
    {
        $qb = $this->createQueryBuilder('m');

        $qb->innerJoin('m.product', 'product')
            ->andWhere('product.product_id = :productId')
            ->setParameter('productId', $product->getProductId());

        if (!is_null($membership)) {
            $qb->innerJoin('m.membership', 'membership')
                ->andWhere('membership.membership_id = :membershipId')
                ->addOrderBy('membership.membership_id')
                ->setParameter('membershipId', $membership->getMembershipId());
        } else {

            $qb->andWhere('m.membership is null');
        }

        return $qb;
    }

    /**
     * Define query builder for deleteByProduct()
     *
     * @param \XLite\Model\Product $product Product entity
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineDeleteByProductQuery(\XLite\Model\Product $product)
    {
        $qb = $this->getQueryBuilder()->delete($this->_entityName, 'm');

        $this->prepareCndProduct($qb, $product);

        return $qb;
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $qb      Query builder to prepare
     * @param \XLite\Model\Product       $product Condition data
     *
     * @return void
     */
    protected function prepareCndProduct(\Doctrine\ORM\QueryBuilder $qb, \XLite\Model\Product $product)
    {
        $qb->andWhere('m.product = :product')
            ->setParameter('product', $product);
    }
}
