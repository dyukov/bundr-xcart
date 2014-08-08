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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\CDev\Wholesale\Model\Repo;

/**
 * WholesalePrice model repository
 */
class WholesalePrice extends \XLite\Model\Repo\ARepo
{
    /**
     * Allowable search params
     */
    const P_MEMBERSHIP          = 'membership';
    const P_PRODUCT             = 'product';
    const P_QTY                 = 'quantity';
    const P_MIN_QTY             = 'minQuantity';
    const P_ORDER_BY            = 'orderBy';
    const P_ORDER_BY_MEMBERSHIP = 'orderByMembership';

    const P_LIMIT               = 'limit';


    // {{{ Search wholesale prices methods

    /**
     * Search for prices
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only count OPTIONAL
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function search(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $queryBuilder = $this->createQueryBuilder('w');
        $this->currentSearchCnd = $cnd;

        $membershipRelation = false;

        foreach ($this->currentSearchCnd as $key => $value) {
            $this->callSearchConditionHandler($value, $key, $queryBuilder, $countOnly);

            if (in_array($key, array(self::P_MEMBERSHIP, self::P_ORDER_BY_MEMBERSHIP))) {
                $membershipRelation = true;
            }
        }

        if ($membershipRelation) {
            $queryBuilder->leftJoin('w.membership', 'membership');
        }

        return $countOnly
            ? $this->searchCount($queryBuilder)
            : $this->searchResult($queryBuilder);
    }


    /**
     * Search count only routine.
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder routine
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    protected function searchCount(\Doctrine\ORM\QueryBuilder $qb)
    {
        $qb->select('COUNT(w.id)');

        return intval($qb->getSingleScalarResult());
    }

    /**
     * Search result routine.
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder routine
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    protected function searchResult(\Doctrine\ORM\QueryBuilder $qb)
    {
        return $qb->getResult();
    }

    /**
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        return array(
            self::P_MEMBERSHIP,
            self::P_PRODUCT,
            self::P_QTY,
            self::P_MIN_QTY,
            self::P_ORDER_BY,
            self::P_ORDER_BY_MEMBERSHIP,
            self::P_LIMIT,
        );
    }

    /**
     * Check if param can be used for search
     *
     * @param string $param Name of param to check
     *
     * @return boolean
     */
    protected function isSearchParamHasHandler($param)
    {
        return in_array($param, $this->getHandlingSearchParams());
    }

    /**
     * Call corresponded method to handle a search condition
     *
     * @param mixed                      $value        Condition data
     * @param string                     $key          Condition name
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param boolean                    $countOnly    Return items list or only count OPTIONAL
     *
     * @return void
     */
    protected function callSearchConditionHandler($value, $key, \Doctrine\ORM\QueryBuilder $queryBuilder, $countOnly)
    {
        if ($this->isSearchParamHasHandler($key)) {
            $this->{'prepareCnd' . ucfirst($key)}($queryBuilder, $value, $countOnly);
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndMembership(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        if (!empty($value)) {

            if (is_object($value)) {
                $value = $value->getMembershipId();
            }

            $cnd = new \Doctrine\ORM\Query\Expr\Orx();
            $cnd->add('membership.membership_id = :membershipId');
            $cnd->add('w.membership IS NULL');

            $queryBuilder->andWhere($cnd)
                ->setParameter('membershipId', $value);

        } else {
            $queryBuilder->andWhere('w.membership IS NULL');
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndProduct(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        if ($value instanceOf \XLite\Model\Product) {
            $value = $value->getProductId();
        }

        $queryBuilder->leftJoin('w.product', 'product')
            ->andWhere('product.product_id = :productId')
            ->setParameter('productId', $value);
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndQuantity(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        $cnd = new \Doctrine\ORM\Query\Expr\Orx();
        $cnd->add('w.quantityRangeEnd >= :qty');
        $cnd->add('w.quantityRangeEnd = 0');

        $queryBuilder->andWhere('w.quantityRangeBegin <= :qty')
            ->andWhere($cnd)
            ->setParameter('qty', $value);
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndMinQuantity(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        $cnd = new \Doctrine\ORM\Query\Expr\Orx();
        $cnd->add('w.quantityRangeEnd >= :minQty');
        $cnd->add('w.quantityRangeEnd = 0');

        $queryBuilder->andWhere($cnd)
            ->setParameter('minQty', $value);
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndOrderBy(\Doctrine\ORM\QueryBuilder $queryBuilder, array $value, $countOnly)
    {
        if (!$countOnly) {
            list($sort, $order) = $value;

            $queryBuilder->addOrderBy($sort, $order);
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndOrderByMembership(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        $this->prepareCndOrderBy($queryBuilder, array('membership.membership_id', $value ? 'ASC' : 'DESC'), $countOnly);
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     *
     * @return void
     */
    protected function prepareCndLimit(\Doctrine\ORM\QueryBuilder $queryBuilder, array $value)
    {
        $queryBuilder->setFrameResults($value);
    }

    // }}}

    // {{{ Correct quantityRangeEnd values

    /**
     * Re-calculate quantityRangeEnd value for each price
     *
     * @param \XLite\Model\Product $product Product object
     *
     * @return void
     */
    public function correctQuantityRangeEnd($product)
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{self::P_PRODUCT} = $product->getProductId();
        $cnd->{self::P_ORDER_BY} = array('w.quantityRangeBegin', 'ASC');

        // Get all prices
        $allPrices = $this->search($cnd);

        if ($allPrices) {

            // Calculate new quantityRangeEnd values for all prices...

            $membershipsHash = array();
            $maxQuantities = array();

            // Get hash of quantityRangeBegin for all prices (group by membership)
            foreach ($allPrices as $key => $price) {
                $membershipId = $price->getMembership() ? $price->getMembership()->getMembershipId() : 0;
                $membershipsHash[$membershipId][$key] = $price->getQuantityRangeBegin();
            }

            // Max allowed value for quantityRangeEnd
            $absMaxQuantity = pow(10, 16);

            // Find quantityRangeEnd for each price and store it in array $maxQuantities
            foreach ($membershipsHash as $membershipId => $membershipDiscounts) {

                foreach ($membershipDiscounts as $priceKey => $minQuantity) {

                    $maxQuantity = $absMaxQuantity;

                    foreach ($membershipDiscounts as $quantity) {
                        if ($quantity > $minQuantity && $quantity < $maxQuantity) {
                            $maxQuantity = $quantity - 1;
                        }
                    }

                    if ($maxQuantity == $absMaxQuantity) {
                        $maxQuantity = 0;
                    }

                    $maxQuantities[$priceKey] = $maxQuantity;
                }
            }

            $needUpdate = false;

            // Update quantityRangeEnd value if it differs from current value
            foreach ($allPrices as $key => $price) {
                if ($price->getQuantityRangeEnd() != $maxQuantities[$key]) {
                    $price->setQuantityRangeEnd($maxQuantities[$key]);
                    \XLite\Core\Database::getEM()->persist($price);
                    $needUpdate = true;
                }
            }

            if ($needUpdate) {
                \XLite\Core\Database::getEM()->flush();
            }
        } // if ($allPrices)
    }

    // }}}

    // {{{ Methods to adjust default prices

    /**
     * Update default product price (for 1 item and all customers)
     *
     * @param \XLite\Model\Product $product Product object
     *
     * @return void
     */
    public function updateDefaultProductPrice(\XLite\Model\Product $product)
    {
        $cnd = new \XLite\Core\CommonCell();

        $cnd->{self::P_PRODUCT} = $product;
        $cnd->{self::P_MEMBERSHIP} = null;
        $cnd->{self::P_QTY} = 1;
        $cnd->{self::P_ORDER_BY} = array('w.quantityRangeBegin', 'ASC');
        $cnd->{self::P_LIMIT}   = array(0, 1);

        $result = $this->search($cnd);

        if (is_array($result)) {
            $result = array_shift($result);
        }

        if ($result && $result->getPrice() != $product->getPrice()) {
            $product->setPrice($result->getPrice());
            \XLite\Core\Database::getEM()->persist($product);
            \XLite\Core\Database::getEM()->flush();
        }
    }

    /**
     * Update (or insert) wholesale price for 1 item and all customers
     *
     * @param \XLite\Model\Product $product ____param_comment____
     *
     * @return void
     */
    public function updateDefaultWholesalePrice(\XLite\Model\Product $product)
    {
        $cnd = new \XLite\Core\CommonCell();

        $cnd->{self::P_PRODUCT} = $product;
        $cnd->{self::P_MEMBERSHIP} = null;
        $cnd->{self::P_QTY} = 1;
        $cnd->{self::P_ORDER_BY} = array('w.quantityRangeBegin', 'ASC');
        $cnd->{self::P_LIMIT}   = array(0, 1);

        $result = $this->search($cnd);

        if (!empty($result)) {
            $result = array_shift($result);

        } else {
            $result = new \XLite\Module\CDev\Wholesale\Model\WholesalePrice();
            $result->setProduct($product);
        }

        if ($result->getPrice() != $product->getPrice()) {

            $result->setPrice($product->getPrice());

            \XLite\Core\Database::getEM()->persist($result);
            \XLite\Core\Database::getEM()->flush();
        }
    }

    // }}}

    // {{{ Additional helper methods

    /**
     * Return price under amount and membership conditions
     *
     * @param \XLite\Model\Product    $product    Product object
     * @param integer                 $amount     Quantity of product
     * @param \XLite\Model\Membership $membership Membership object OPTIONAL
     *
     * @return float Product price
     * @return null  Null price means the default value for specific price type must be used
     */
    public function getPrice(\XLite\Model\Product $product, $amount, $membership = null)
    {
        $cnd = new \XLite\Core\CommonCell();

        $cnd->{self::P_MEMBERSHIP} = $membership;
        $cnd->{self::P_PRODUCT} = $product;
        $cnd->{self::P_QTY}     = $amount;
        $cnd->{self::P_ORDER_BY_MEMBERSHIP} = false;

        $prices = $this->search($cnd);

        return isset($prices[0]) ? $prices[0]->getPrice() : null;
    }

    /**
     * Return wholesale prices for the given product
     *
     * @param \XLite\Model\Product    $product    Product object
     * @param \XLite\Model\Membership $membership Membership object OPTIONAL
     *
     * @return \Doctrine\ORM\PersistentCollection
     */
    public function getWholesalePrices(\XLite\Model\Product $product, $membership = null)
    {
        $cnd = new \XLite\Core\CommonCell();

        $minQty = $product->getMinQuantity($membership);

        $cnd->{self::P_PRODUCT}    = $product;
        $cnd->{self::P_MEMBERSHIP} = $membership;
        $cnd->{self::P_MIN_QTY}    = $minQty;
        $cnd->{self::P_ORDER_BY}   = array('w.quantityRangeBegin', 'ASC');
        $cnd->{self::P_ORDER_BY_MEMBERSHIP} = false;

        $prices = $this->search($cnd);

        if (1 < $minQty) {
            foreach ($prices as $key => $price) {
                if ($prices[$key]->getQuantityRangeBegin() < $minQty) {
                    $prices[$key]->setQuantityRangeBegin($minQty);
                }
            }
        }

        if (isset($membership) && !empty($prices)) {

            // Process wholesale prices if membership is not null

            $minMembershipRangeBegin = 0;

            // Prepare arrays of data
            foreach ($prices as $key => $price) {

                $m = $price->getMembership();

                if (isset($m)) {

                    if (0 == $minMembershipRangeBegin) {
                        $minMembershipRangeBegin = $price->getQuantityRangeBegin();
                    }

                    $membershipRanges[$price->getQuantityRangeBegin()] = $price->getQuantityRangeEnd();

                } else {
                    $nonMembershipRanges[$key] = array($price->getQuantityRangeBegin(), $price->getQuantityRangeEnd());
                }
            }

            // Remove ranges for "All customers" which are subset of ranges for $membership
            if (0 < $minMembershipRangeBegin) {

                foreach ($nonMembershipRanges as $key => $qtyData) {

                    if ($qtyData[0] < $minMembershipRangeBegin) {

                        if ($qtyData[1] > $minMembershipRangeBegin || 0 == $qtyData[1]) {
                            $prices[$key]->setQuantityRangeEnd($minMembershipRangeBegin - 1);
                        }

                    } else {
                        unset($prices[$key]);
                    }
                }
            }
        }

        // Transform qty ranges with same price to the single range
        if (!empty($prices)) {

            $currentKey = null;

            foreach ($prices as $key => $price) {

                if (!isset($currentKey)) {
                    $currentKey = $key;

                    if ($prices[$currentKey]->getQuantityRangeBegin() < $minQty) {
                        $prices[$currentKey]->setQuantityRangeBegin($minQty);
                    }

                    continue;
                }

                if ($prices[$currentKey]->getPrice() == $price->getPrice()) {
                    $prices[$currentKey]->setQuantityRangeEnd($price->getQuantityRangeEnd());
                    unset($prices[$key]);

                } else {
                    $currentKey = $key;
                }
            }
        }

        if (1 == count($prices)) {
            $prices = array();
        }

        return $prices;
    }

    /**
     * Check if the product has any wholesale price
     *
     * @param \XLite\Model\Product    $product    Product object
     *
     * @return \Doctrine\ORM\PersistentCollection
     */
    public function hasWholesalePrice(\XLite\Model\Product $product)
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{self::P_PRODUCT} = $product;

        return count($this->search($cnd)) > 1;
    }

    // }}}
}
