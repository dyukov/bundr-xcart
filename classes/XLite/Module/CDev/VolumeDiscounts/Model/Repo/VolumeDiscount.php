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

namespace XLite\Module\CDev\VolumeDiscounts\Model\Repo;

/**
 * VolumeDiscount repository
 */
class VolumeDiscount extends \XLite\Model\Repo\ARepo
{
    /**
     * Allowable search params
     */
    const P_MEMBERSHIP = 'membership';
    const P_SUBTOTAL = 'subtotal';
    const P_SUBTOTAL_ADV = 'subtotalAdv';
    const P_MIN_VALUE = 'minValue';
    const P_ORDER_BY_SUBTOTAL = 'orderBySubtotal';
    const P_ORDER_BY_MEMBERSHIP = 'orderByMembership';
    const P_LIMIT = 'limit';


    /**
     * Find one similar discount 
     * 
     * @param \XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount $model Discount
     *  
     * @return \XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount
     */
    public function findOneSimilarDiscount(\XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount $model)
    {
        return $this->definefindOneSimilarDiscountQuery($model)->getSingleResult();
    }

    /**
     * Define query for 'findOneSimilarDiscount' method
     * 
     * @param \XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount $model Discount
     *  
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function definefindOneSimilarDiscountQuery(\XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount $model)
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('v.subtotalRangeBegin = :rangeBegin')
            ->setParameter('rangeBegin', $model->getSubtotalRangeBegin())
            ->setMaxResults(1);

        if ($model->getMembership()) {
            $qb->andWhere('v.membership = :membership')
                ->setParameter('membership', $model->getMembership());

        } else {
            $qb->andWhere('v.membership IS NULL');
        }

        return $qb;
    }

    // {{{ Search discounts methods

    /**
     * Search for discounts
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only count OPTIONAL
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function search(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $queryBuilder = $this->createQueryBuilder('v');
        $this->currentSearchCnd = $cnd;

        $membershipRelation = false;

        foreach ($this->currentSearchCnd as $key => $value) {
            $this->callSearchConditionHandler($value, $key, $queryBuilder, $countOnly);
            if (in_array($key, array(self::P_MEMBERSHIP, self::P_ORDER_BY_MEMBERSHIP))) {
                $membershipRelation = true;
            }
        }

        if ($membershipRelation) {
            $queryBuilder->leftJoin('v.membership', 'membership');
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
        $qb->select('COUNT(v.id)');

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
            self::P_SUBTOTAL,
            self::P_SUBTOTAL_ADV,
            self::P_MIN_VALUE,
            self::P_ORDER_BY_SUBTOTAL,
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
        if (isset($value)) {
            $cnd = new \Doctrine\ORM\Query\Expr\Orx();
            $cnd->add('membership.membership_id = :membershipId');
            $cnd->add('v.membership IS NULL');

            $queryBuilder->andWhere($cnd)
                ->setParameter('membershipId', $value);

        } else {
            $queryBuilder->andWhere('v.membership IS NULL');
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
    protected function prepareCndSubtotal(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        $cnd = new \Doctrine\ORM\Query\Expr\Orx();
        $cnd->add('v.subtotalRangeEnd > :subtotal');
        $cnd->add('v.subtotalRangeEnd = 0');

        $queryBuilder->andWhere('v.subtotalRangeBegin <= :subtotal')
            ->andWhere($cnd)
            ->setParameter('subtotal', $value);
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
    protected function prepareCndSubtotalAdv(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        $queryBuilder->andWhere('v.subtotalRangeBegin > :subtotal')
            ->setParameter('subtotal', $value);
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
    protected function prepareCndMinValue(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        $queryBuilder->andWhere('v.value > :value')
            ->setParameter('value', $value);
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
    protected function prepareCndOrderBySubtotal(\Doctrine\ORM\QueryBuilder $queryBuilder, array $value, $countOnly)
    {
        $this->prepareCndOrderBy($queryBuilder, $value, $countOnly);
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
    protected function prepareCndOrderByMembership(\Doctrine\ORM\QueryBuilder $queryBuilder, array $value, $countOnly)
    {
        $this->prepareCndOrderBy($queryBuilder, $value, $countOnly);
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

    // {{{ Correct subtotalRangeEnd values

    /**
     * Re-calculate subtotalRangeEnd value for each discount
     * 
     * @return void
     */
    public function correctSubtotalRangeEnd()
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{self::P_ORDER_BY_SUBTOTAL} = array('v.subtotalRangeBegin', 'ASC');

        // Get all discounts
        $allDiscounts = $this->search($cnd);

        if ($allDiscounts) {

            // Calculate new subtotalRangeEnd values for all discounts...

            $membershipsHash = array();
            $maxSubtotals = array();

            // Get hash of subtotalRangeBegin for all discounts (group by membership)
            foreach ($allDiscounts as $key => $discount) {
                $membershipId = $discount->getMembership() ? $discount->getMembership()->getMembershipId() : 0;
                $membershipsHash[$membershipId][$key] = $discount->getSubtotalRangeBegin();
            }

            // Max allowed value for subtotalRangeEnd
            $absMaxSubtotal = pow(10, 16);

            // Find subtotalRangeEnd for each discount and store it in array $maxSubtotals
            foreach ($membershipsHash as $membershipId => $membershipDiscounts) {

                foreach ($membershipDiscounts as $discountKey => $minSubtotal) {
                    
                    $maxSubtotal = $absMaxSubtotal;

                    foreach ($membershipDiscounts as $subtotal) {
                        if ($subtotal > $minSubtotal && $subtotal < $maxSubtotal) {
                            $maxSubtotal = $subtotal;
                        }
                    }

                    if ($maxSubtotal == $absMaxSubtotal) {
                        $maxSubtotal = 0;
                    }

                    $maxSubtotals[$discountKey] = $maxSubtotal;
                }
            }

            $needUpdate = false;

            // Update subtotalRangeEnd value if it differs from current value
            foreach ($allDiscounts as $key => $discount) {
                if ($discount->getSubtotalRangeEnd() != $maxSubtotals[$key]) {
                    $discount->setSubtotalRangeEnd($maxSubtotals[$key]);
                    \XLite\Core\Database::getEM()->persist($discount);
                    $needUpdate = true;
                }
            }

            if ($needUpdate) {
                \XLite\Core\Database::getEM()->flush();
            }
        } // if ($allDiscounts)
    }

    // }}}

    // {{{ Find suitable discount methods

    /**
     * Get first discount suitable for specified subtotal 
     * 
     * @param float                   $subtotal   Subtotal
     * @param \XLite\Model\Membership $membership Membership object
     *  
     * @return \XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount
     */
    public function getFirstDiscountBySubtotal($subtotal, $membership)
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{self::P_SUBTOTAL} = $subtotal;
        $cnd->{self::P_MEMBERSHIP} = $membership ? $membership->getMembershipId() : null;
        if ($membership) {
            $cnd->{self::P_ORDER_BY_MEMBERSHIP} = array('membership.membership_id', 'DESC');
        }
        $cnd->{self::P_ORDER_BY_SUBTOTAL} = array('v.subtotalRangeBegin', 'ASC');

        $discounts = $this->search($cnd);

        if ($discounts) {
            $discounts = array_shift($discounts);

        } else {
            $discounts = null;
        }

        return $discounts;
    }

    // }}}

    // {{{ Find next suitable discount method

    /**
     * Get next discount suitable for specified subtotal 
     * 
     * @param float                   $subtotal   Subtotal
     * @param \XLite\Model\Membership $membership Membership object
     *  
     * @return \XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount
     */
    public function getNextDiscount($subtotal, $membership)
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{self::P_SUBTOTAL_ADV} = $subtotal;
        $cnd->{self::P_MIN_VALUE} = 0;
        $cnd->{self::P_MEMBERSHIP} = $membership ? $membership->getMembershipId() : null;
        $cnd->{self::P_ORDER_BY_SUBTOTAL} = array('v.subtotalRangeBegin', 'ASC');
        $cnd->{self::P_ORDER_BY_MEMBERSHIP} = array('membership.membership_id', 'DESC');

        $discounts = $this->search($cnd);

        if ($discounts) {
            $discounts = array_shift($discounts);

        } else {
            $discounts = null;
        }

        return $discounts;
    }

    // }}}
}
