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

namespace XLite\Model\Repo;

/**
 * The "order_item" model repository
 */
class OrderItem extends \XLite\Model\Repo\ARepo
{
    /**
     * Get detailed foreign keys
     *
     * @return array
     */
    protected function getDetailedForeignKeys()
    {
        $list = parent::getDetailedForeignKeys();

        $list[] = array(
            'fields'          => array('object_id'),
            'referenceRepo'   => 'XLite\Model\Product',
            'referenceFields' => array('product_id'),
            'delete'          => 'SET NULL',
        );

        return $list;
    }

    // {{{ Functions to grab top selling products data

    /**
     * Get top sellers depending on certain condition
     *
     * @param \XLite\Core\CommonCell $cnd Conditions
     *
     * @return \Doctrine\ORM\PersistentCollection
     */
    public function getTopSellers(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $result = $this->prepareTopSellersCondition($cnd)->getResult();

        return $countOnly ? count($result) : $result;
    }

    /**
     * Returns the top sellers count (used on the dashboard)
     *
     * @param integer $currencyId Currency Id
     *
     * @return integer
     */
    public function getTopSellersCount($currencyId)
    {
        return $this->getTopSellers($this->prepareTopSellersCountCnd($currencyId), true);
    }

    /**
     * Prepare the top sellers count condition
     *
     * @param integer $currencyId Currency Id
     *
     * @return \XLite\Core\CommonCell
     */
    protected function prepareTopSellersCountCnd($currencyId)
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->currency = $currencyId;

        return $cnd;
    }

    /**
     * Prepare top sellers search condition
     *
     * @param \XLite\Core\CommonCell $cnd Conditions
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function prepareTopSellersCondition(\XLite\Core\CommonCell $cnd)
    {
        list($start, $end) = $cnd->date;

        $qb = $this->createQueryBuilder();

        $qb->addSelect('SUM(o.amount) as cnt')
            ->innerJoin('o.order', 'o1')
            ->innerJoin('o1.paymentStatus', 'ps')
            ->innerJoin('o1.currency', 'currency', 'WITH', 'currency.currency_id = :currency_id')
            ->addSelect('o1.date')
            ->andWhere($qb->expr()->in('ps.code', \XLite\Model\Order\Status\Payment::getPaidStatuses()))
            ->setParameter('currency_id', $cnd->currency)
            ->setMaxResults($cnd->limit)
            ->addGroupBy('o.sku')
            ->addOrderBy('cnt', 'desc')
            ->addOrderBy('o.name', 'asc');

        if (0 < $start) {
            $qb->andWhere('o1.date >= :start')
                ->setParameter('start', $start);
        }

        if (0 < $end) {
            $qb->andWhere('o1.date < :end')
                ->setParameter('end', $end);
        }

        return $qb;
    }

    // }}}
}
