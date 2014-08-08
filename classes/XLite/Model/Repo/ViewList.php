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

namespace XLite\Model\Repo;

/**
 * View list repository
 */
class ViewList extends \XLite\Model\Repo\ARepo
{
    /**
     * Repository type
     *
     * @var string
     */
    protected $type = self::TYPE_INTERNAL;

    /**
     * Default 'order by' field name
     *
     * @var string
     */
    protected $defaultOrderBy = array(
        'weight' => true,
        'child'  => true,
        'tpl'    => true,
    );

    /**
     * Columns' character sets definitions
     *
     * @var array
     */
    protected $columnsCharSets = array(
        'class' => 'latin1',
        'list'  => 'latin1',
        'zone'  => 'latin1',
        'child' => 'latin1',
        'tpl'   => 'latin1',
    );


    /**
     * Find class list
     *
     * @param string $list List name
     * @param string $zone Current interface name OPTIONAL
     *
     * @return array
     */
    public function findClassList($list, $zone = \XLite\Model\ViewList::INTERFACE_CUSTOMER)
    {
        $data = $this->getFromCache('class_list', array('list' => $list, 'zone' => $zone));

        if (!isset($data)) {
            $data = $this->defineClassListQuery($list, $zone)->getResult();
            $this->saveToCache($data, 'class_list', array('list' => $list, 'zone' => $zone));
        }

        return $data;
    }

    /**
     * Find view list by tempalte pattern and list name
     *
     * @param string $tpl  Tempalte pattern
     * @param string $list List name
     *
     * @return \XLite\Model\ViewList|void
     */
    public function findOneByTplAndList($tpl, $list)
    {
        return $this->defineOneByTplAndListQuery($tpl, $list)->getSingleResult();
    }


    /**
     * Define cache cells
     *
     * @return array
     */
    protected function defineCacheCells()
    {
        $list = parent::defineCacheCells();

        $list['class_list'] = array(
            self::ATTRS_CACHE_CELL => array('list', 'zone'),
        );

        return $list;
    }

    /**
     * Define query for findOneByTplAndList() method
     *
     * @param string $tpl  Tempalte pattern
     * @param string $list List name
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineOneByTplAndListQuery($tpl, $list)
    {
        return $this->createQueryBuilder()
            ->andWhere('v.tpl LIKE :tpl AND v.list = :list')
            ->setParameter('tpl', $tpl)
            ->setParameter('list', $tpl);
    }

    /**
     * Define query builder for findClassList()
     *
     * @param string $list Class list name
     * @param string $zone Current interface name
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineClassListQuery($list, $zone)
    {
        return $this->createQueryBuilder()
            ->where('v.list = :list AND v.zone IN (:zone, :empty)')
            ->setParameter('empty', '')
            ->setParameter('list', $list)
            ->setParameter('zone', $zone);
    }
}
