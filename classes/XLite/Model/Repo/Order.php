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
 * Order repository
 */
class Order extends \XLite\Model\Repo\ARepo
{
    /**
     * Cart TTL (in seconds)
     */
    const ORDER_TTL = 86400;

    /**
     * In progress orders TTL (in seconds)
     */
    const IN_PROGRESS_ORDER_TTL = 3600;

    /**
     * Allowable search params
     */
    const P_ORDER_ID          = 'orderId';
    const P_PROFILE_ID        = 'profileId';
    const P_PROFILE           = 'profile';
    const P_EMAIL             = 'email';
    const P_PAYMENT_STATUS    = 'paymentStatus';
    const P_SHIPPING_STATUS   = 'shippingStatus';
    const P_DATE              = 'date';
    const P_CURRENCY          = 'currency';
    const P_ORDER_BY          = 'orderBy';
    const P_LIMIT             = 'limit';
    const P_ORDER_NUMBER      = 'orderNumber';
    const SEARCH_DATE_RANGE   = 'dateRange';
    const SEARCH_SUBSTRING    = 'substring';
    const SEARCH_ACCESS_LEVEL = 'accessLevel';

    /**
     * currentSearchCnd
     *
     * @var \XLite\Core\CommonCell
     */
    protected $currentSearchCnd = null;


    /**
     * Find all expired temporary orders
     *
     * @return \Doctrine\Common\Collection\ArrayCollection
     */
    public function findAllExpiredTemporaryOrders()
    {
        return $this->getOrderTTL()
            ? $this->defineAllExpiredTemporaryOrdersQuery()->getResult()
            : array();
    }

    /**
     * Find all expired 'in progress' orders
     *
     * @return \Doctrine\Common\Collection\ArrayCollection
     */
    public function findAllExpiredInProgressOrders()
    {
        return $this->defineAllExpiredInProgressOrdersQuery()->getResult();
    }

    /**
     * Get orders statistics data: count and sum of orders
     *
     * @param integer $startDate Start date timestamp
     * @param integer $endDate   End date timestamp OPTIONAL
     *
     * @return array
     */
    public function getOrderStats($startDate, $endDate = 0)
    {
        $result = $this->defineGetOrderStatsQuery($startDate, $endDate)->getSingleResult();

        return $result;
    }

    /**
     * Create a new QueryBuilder instance that is prepopulated for this entity name
     *
     * @param string  $alias      Table alias OPTIONAL
     * @param boolean $placedOnly Use only orders or orders + carts OPTIONAL
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilder($alias = null, $placedOnly = true)
    {
        $result = parent::createQueryBuilder($alias);

        if ($placedOnly) {
            $result->andWhere('o INSTANCE OF XLite\Model\Order');
        }

        return $result;
    }

    /**
     * Orders collect garbage
     *
     * @return void
     */
    public function collectGarbage()
    {
        // Remove old temporary orders
        $list = $this->findAllExpiredTemporaryOrders();
        if (count($list)) {
            foreach ($list as $order) {
                \XLite\Core\Database::getEM()->remove($order);
            }

            \XLite\Core\Database::getEM()->flush();

            // Log operation only in debug mode
            \XLite\Logger::getInstance()->log(
                \XLite\Core\Translation::getInstance()->translate(
                    'X expired shopping cart(s) have been successfully removed',
                    array('count' => count($list))
                )
            );
        }
    }

    /**
     * Orders collect garbage
     *
     * @return void
     */
    public function updateExpiredInProgressOrders()
    {
        // Change old 'In progress' orders to the 'Expired' status
        $list = $this->findAllExpiredInProgressOrders();
        if (count($list)) {
            foreach ($list as $order) {
                $order->setPaymentStatus(\XLite\Model\Order\Status\Payment::STATUS_DECLINED);
                $order->setShippingStatus(\XLite\Model\Order\Status\Shipping::STATUS_WILL_NOT_DELIVER);
            }

            \XLite\Core\Database::getEM()->flush();
        }
    }

    /**
     * Common search
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function search(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $queryBuilder = $this->createQueryBuilder()
            ->innerJoin('o.profile', 'p')
            ->leftJoin('o.orig_profile', 'op');
        $this->currentSearchCnd = $cnd;

        foreach ($this->currentSearchCnd as $key => $value) {
            if (self::P_LIMIT != $key || !$countOnly) {
                $this->callSearchConditionHandler($value, $key, $queryBuilder);
            }
        }

        if ($countOnly) {
            // We remove all order-by clauses since it is not used for count-only mode
            $queryBuilder->select('COUNT(o.order_id)')->orderBy('o.order_id');
            $result = intval($queryBuilder->getSingleScalarResult());

        } else {
            $result = $queryBuilder->getOnlyEntities();
        }

        return $result;
    }

    /**
     * Create a QueryBuilder instance for getSearchTotals()
     *
     * @param \XLite\Core\CommonCell $cnd Search condition
     *
     * @return \Doctrine\ORM\PersistentCollection
     */
    public function defineGetSearchTotalQuery(\XLite\Core\CommonCell $cnd)
    {
        $queryBuilder = $this->createQueryBuilder()
            ->select('SUM(o.total) as orders_total')
            ->addSelect('c.currency_id as currency_id')
            ->innerJoin('o.profile', 'p')
            ->innerJoin('o.currency', 'c')
            ->leftJoin('o.orig_profile', 'op')
            ->addGroupBy('c.currency_id')
            ->addOrderBy('orders_total', 'DESC');

        $this->currentSearchCnd = $cnd;

        foreach ($this->currentSearchCnd as $key => $value) {
            if (self::P_LIMIT != $key && self::P_ORDER_BY != $key) {
                $this->callSearchConditionHandler($value, $key, $queryBuilder);
            }
        }

        return $queryBuilder;
    }

    /**
     * Next order number is initialized with the maximum order number
     *
     * @return void
     */
    public function initializeNextOrderNumber()
    {
        \XLite\Core\Database::getRepo('XLite\Model\Config')->createOption(
            array(
                'category'  => 'General',
                'name'      => 'order_number_counter',
                'value'     => $this->getMaxOrderNumber(),
            )
        );
    }

    /**
     * The maximum order number
     *
     * @return integer
     */
    public function getMaxOrderNumber()
    {
        $maxIdOrder = $this->findBy(array(), array('order_id' => 'desc'), 1);
        return $maxIdOrder[0]->getOrderId() + 1;
    }

    /**
     * The next order number is used only for orders.
     * This generator checks the  field for independent ID for orders only
     *
     * @return integer
     */
    public function findNextOrderNumber()
    {
        if (!\XLite\Core\Config::getInstance()->General->order_number_counter) {
            $this->initializeNextOrderNumber();
        }

        $orderNumber = \XLite\Core\Database::getRepo('XLite\Model\Config')
            ->findOneBy(array('name' => 'order_number_counter', 'category' => 'General'));

        $value = $orderNumber->getValue();

        $lastOrderNumber = $this->defineMaxOrderNumberQuery()->getSingleScalarResult();

        if ($lastOrderNumber) {
            $value = max($value, $lastOrderNumber + 1);
        }

        \XLite\Core\Database::getRepo('XLite\Model\Config')->update(
            $orderNumber, array('value' => $value + 1)
        );

        return $value;
    }

    /**
     * Selects the last maximum order number field.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function defineMaxOrderNumberQuery()
    {
        return $this->createQueryBuilder()
            ->select('o.orderNumber')
            ->andWhere('o INSTANCE OF XLite\Model\Order')
            ->andWhere('o.orderNumber != :null')
            ->addOrderBy('o.order_id', 'desc')
            ->setParameter('null', 'NULL')
            ->setMaxResults(1);
    }

    /**
     * Create a QueryBuilder instance for getOrderStats()
     *
     * @param integer $startDate Start date timestamp
     * @param integer $endDate   End date timestamp
     *
     * @return \Doctrine\ORM\QueryBuilder
     */

    protected function defineGetOrderStatsQuery($startDate, $endDate)
    {
        $qb = $this->createQueryBuilder()
            ->select('COUNT(o.order_id) as orders_count')
            ->addSelect('SUM(o.total) as orders_total');

        $this->prepareCndDate($qb, array($startDate, $endDate));
        $this->prepareCndPaymentStatus($qb, \XLite\Model\Order\Status\Payment::getOpenStatuses());

        return $qb;
    }

    /**
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        return array(
            static::P_ORDER_ID,
            static::P_PROFILE_ID,
            static::P_PROFILE,
            static::P_EMAIL,
            static::P_PAYMENT_STATUS,
            static::P_SHIPPING_STATUS,
            static::P_DATE,
            static::P_CURRENCY,
            static::P_ORDER_BY,
            static::P_LIMIT,
            static::P_ORDER_NUMBER,
            static::SEARCH_DATE_RANGE,
            static::SEARCH_SUBSTRING,
            static::SEARCH_ACCESS_LEVEL,
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
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param integer                    $value        Condition data
     *
     * @return void
     */
    protected function prepareCndOrderId(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            $queryBuilder->andWhere('o.order_id = :order_id')
                ->setParameter('order_id', $value);
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param integer                    $value        Condition data
     *
     * @return void
     */
    protected function prepareCndOrderNumber(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            $queryBuilder->andWhere('o.orderNumber = :orderNumber')
                ->setParameter('orderNumber', $value);
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param integer                    $value        Condition data
     *
     * @return void
     */
    protected function prepareCndAccessLevel(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            switch ($value) {
                case \XLite\View\FormField\Select\Order\CustomerAccessLevel::ACCESS_LEVEL_ANONYMOUS:
                    $anonymous = 1;

                    break;

                case \XLite\View\FormField\Select\Order\CustomerAccessLevel::ACCESS_LEVEL_REGISTERED:
                    $anonymous = 0;

                    break;

                default:
                    $anonymous = '';

                    break;
            }

            if ('' !== $anonymous) {
                $cnd = new \Doctrine\ORM\Query\Expr\Orx();

                $anonymousCnd = new \Doctrine\ORM\Query\Expr\Andx();

                $anonymousCnd->add('op.profile_id IS NULL');
                $anonymousCnd->add('p.anonymous = :accessLevel');

                $cnd->add('op.anonymous = :accessLevel');
                $cnd->add($anonymousCnd);

                $queryBuilder->andWhere($cnd)
                    ->setParameter('accessLevel', $anonymous);
            }

        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param integer                    $value        Condition data
     *
     * @return void
     */
    protected function prepareCndDateRange(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            list($start, $end) = \XLite\View\FormField\Input\Text\DateRange::convertToArray($value);
            if ($start) {
                $queryBuilder->andWhere('o.date >= :start')
                    ->setParameter('start', $start);
            }

            if ($end) {
                $queryBuilder->andWhere('o.date <= :end')
                    ->setParameter('end', $end);
            }
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param integer                    $value        Condition data
     *
     * @return void
     */
    protected function prepareCndSubstring(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            $number = $value;
            if (preg_match('/^\d+$/Ss', $number)) {
                $number = intval($number);
            }
            $queryBuilder->andWhere('o.orderNumber = :substring OR p.login LIKE :substringLike')
                ->setParameter('substring', $number)
                ->setParameter('substringLike', '%' . $value . '%');
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param \XLite\Model\Profile       $value        Profile
     *
     * @return void
     */
    protected function prepareCndProfile(\Doctrine\ORM\QueryBuilder $queryBuilder, \XLite\Model\Profile $value)
    {
        if (!empty($value)) {
            $queryBuilder->andWhere('op.profile_id = :opid')
                ->setParameter('opid', $value->getProfileId());
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param integer                    $value        Condition data
     *
     * @return void
     */
    protected function prepareCndProfileId(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            $value = \XLite\Core\Database::getRepo('XLite\Model\Profile')->find($value);
            $queryBuilder->andWhere('o.orig_profile = :orig_profile')
                ->setParameter('orig_profile', $value);
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param string                     $value        Condition data
     *
     * @return void
     */
    protected function prepareCndEmail(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            $queryBuilder->andWhere('p.login LIKE :email')
                ->setParameter('email', '%' . $value . '%');
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param string                     $value        Condition data
     *
     * @return void
     */
    protected function prepareCndPaymentStatus(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {

            if (is_array($value)) {
                $queryBuilder->innerJoin('o.paymentStatus', 'ps')
                    ->andWhere($queryBuilder->expr()->in('ps.code', $value));

            } elseif (is_object($value)) {
                $queryBuilder->andWhere('o.paymentStatus = :paymentStatus')
                    ->setParameter('paymentStatus', $value);

            } elseif (
                is_int($value)
                || (
                    is_string($value)
                    && preg_match('/^[\d]+$/', $value)
                )
            ) {
                $queryBuilder->innerJoin('o.paymentStatus', 'ps')
                    ->andWhere('ps.id = :paymentStatus')
                    ->setParameter('paymentStatus', $value);

            } elseif (is_string($value)) {
                $queryBuilder->innerJoin('o.paymentStatus', 'ps')
                    ->andWhere('ps.code = :paymentStatus')
                    ->setParameter('paymentStatus', $value);
            }
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param string                     $value        Condition data
     *
     * @return void
     */
    protected function prepareCndShippingStatus(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {

            if (is_array($value)) {
                $queryBuilder->innerJoin('o.shippingStatus', 'ss')
                    ->andWhere($queryBuilder->expr()->in('ss.code', $value));

            } elseif (is_object($value)) {
                $queryBuilder->andWhere('o.shippingStatus = :shippingStatus')
                    ->setParameter('shippingStatus', $value);

            } elseif (
                is_int($value)
                || (
                    is_string($value)
                    && preg_match('/^[\d]+$/', $value)
                )
            ) {
                $queryBuilder->innerJoin('o.shippingStatus', 'ss')
                    ->andWhere('ss.id = :shippingStatus')
                    ->setParameter('shippingStatus', $value);

            } elseif (is_string($value)) {
                $queryBuilder->innerJoin('o.shippingStatus', 'ss')
                    ->andWhere('ss.code = :shippingStatus')
                    ->setParameter('shippingStatus', $value);
            }
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data OPTIONAL
     *
     * @return void
     */
    protected function prepareCndDate(\Doctrine\ORM\QueryBuilder $queryBuilder, array $value = null)
    {
        if (is_array($value)) {
            $value = array_values($value);
            $start = empty($value[0]) ? null : intval($value[0]);
            $end = empty($value[1]) ? null : intval($value[1]);

            if ($start) {
                $queryBuilder->andWhere('o.date >= :start')
                    ->setParameter('start', $start);
            }

            if ($end) {
                $queryBuilder->andWhere('o.date <= :end')
                    ->setParameter('end', $end);
            }
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param integer                    $value        Condition data OPTIONAL
     *
     * @return void
     */
    protected function prepareCndCurrency(\Doctrine\ORM\QueryBuilder $queryBuilder, $value = null)
    {
        if ($value) {
            $queryBuilder->innerJoin('o.currency', 'currency', 'WITH', 'currency.currency_id = :currency_id')
                ->setParameter('currency_id', $value);
        }
    }

    /**
     * Return cart TTL
     *
     * @return integer
     */
    protected function getOrderTTL()
    {
        return intval(\XLite\Core\Config::getInstance()->General->cart_ttl) * 86400;
    }

    /**
     * Define query for findAllExpiredTemporaryOrders() method
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineAllExpiredTemporaryOrdersQuery()
    {
        return $this->createQueryBuilder(null, false)
            ->leftJoin('o.orig_profile', 'op')
            ->andWhere('o INSTANCE OF XLite\Model\Cart')
            ->andWhere('op.profile_id IS NULL')
            ->andWhere('o.date < :time')
            ->setParameter('time', \XLite\Core\Converter::time() - $this->getOrderTTL());
    }

    /**
     * Return 'in progress' order TTL
     *
     * @return integer
     */
    protected function getInProgressOrderTTL()
    {
        return self::IN_PROGRESS_ORDER_TTL;
    }

    /**
     * Define query for findAllExpiredInProgressOrders() method
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineAllExpiredInProgressOrdersQuery()
    {
        return $this->createQueryBuilder(null, false)
            ->andWhere('o INSTANCE OF XLite\Model\Cart')
            ->andWhere('o.lastRenewDate < :time')
            ->setParameter('time', \XLite\Core\Converter::time() - $this->getInProgressOrderTTL());
    }

   /**
     * Generate fullname by firstname and lastname values
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     *
     * @return void
     */
    protected function prepareCndOrderByFullname(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        $queryBuilder->leftJoin('p.addresses', 'addresses');
        $this->prepareOrderByAddressField($queryBuilder, 'firstname');
        $this->prepareOrderByAddressField($queryBuilder, 'lastname');

        $queryBuilder->addSelect(
            'CONCAT(CONCAT(orderby_field_value_firstname.value, \' \'),
            orderby_field_value_lastname.value) as fullname'
        );
    }

    /**
     * Prepare fields for fullname value (for 'order by')
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder object
     * @param string                     $fieldName    Field name
     *
     * @return void
     */
    protected function prepareOrderByAddressField(\Doctrine\ORM\QueryBuilder $queryBuilder, $fieldName)
    {
        $addressField = \XLite\Core\Database::getRepo('XLite\Model\AddressField')
            ->findOneBy(array('serviceName' => $fieldName));

        $queryBuilder->leftJoin(
            'addresses.addressFields',
            'orderby_field_value_' . $fieldName,
            \Doctrine\ORM\Query\Expr\Join::WITH,
            'orderby_field_value_' . $fieldName . '.addressField = :' . $fieldName
        )->setParameter($fieldName, $addressField);
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     *
     * @return void
     */
    protected function prepareCndOrderBy(\Doctrine\ORM\QueryBuilder $queryBuilder, array $value)
    {
        list($sort, $order) = $this->getSortOrderValue($value);
        if (!is_array($sort)) {
            $sort = array($sort);
            $order = array($order);
        }
        $queryBuilder->addSelect('INTVAL(o.orderNumber) AS int_order_number');

        foreach ($sort as $key => $sortItem) {
            if ($sortItem == \XLite\View\ItemsList\Model\Order\Admin\Search::SORT_BY_MODE_ID) {
                $sortItem = 'int_order_number';

            } elseif ($sortItem == \XLite\View\ItemsList\Model\Order\Admin\Search::SORT_BY_MODE_CUSTOMER) {
                $this->prepareCndOrderByFullname($queryBuilder);
            }

            $queryBuilder->addOrderBy($sortItem, $order[$key]);
        }
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

    /**
     * Call corresponded method to handle a serch condition
     *
     * @param mixed                      $value        Condition data
     * @param string                     $key          Condition name
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     *
     * @return void
     */
    protected function callSearchConditionHandler($value, $key, \Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        if ($this->isSearchParamHasHandler($key)) {
            $methodName = 'prepareCnd' . ucfirst($key);
            // $methodName is assembled from 'prepareCnd' + key
            $this->$methodName($queryBuilder, $value);

        } else {
            // TODO - add logging here
        }
    }

    /**
     * Get detailed foreign keys
     *
     * @return array
     */
    protected function getDetailedForeignKeys()
    {
        return array(
            array(
                'fields'          => array('orig_profile_id'),
                'referenceRepo'   => 'XLite\Model\Profile',
                'referenceFields' => array('profile_id'),
                'delete'          => 'SET NULL',
            ),
        );
    }

    /**
     * Delete single entity
     *
     * @param \XLite\Model\AEntity $entity Entity to detach
     *
     * @return void
     */
    protected function performDelete(\XLite\Model\AEntity $entity)
    {
        $entity->setOldPaymentStatus(null);
        $entity->setOldShippingStatus(null);

        parent::performDelete($entity);
    }

    // {{{ Export routines

    /**
     * Define export iterator query builder
     *
     * @param integer $position Position
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineExportIteratorQueryBuilder($position)
    {
        return parent::defineExportIteratorQueryBuilder($position)
            ->orderBy('o.date', 'desc');
    }

    // }}}
}
