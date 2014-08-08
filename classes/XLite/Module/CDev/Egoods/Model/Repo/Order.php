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

namespace XLite\Module\CDev\Egoods\Model\Repo;

/**
 * Order  repository
 */
abstract class Order extends \XLite\Model\Repo\Order implements \XLite\Base\IDecorator
{
    /**
     * Find all orders bu profile WithEgoods 
     * 
     * @param \XLite\Model\Profile $profile ____param_comment____
     *  
     * @return void
     */
    public function findAllOrdersWithEgoods(\XLite\Model\Profile $profile = null)
    {
        $list = array();

        foreach ($this->defineFindAllOrdersWithEgoodsQuery($profile)->getResult() as $order) {
            if ($order->getDownloadAttachments()) {
                $list[] = $order;
            }
        }

        return $list;
    }

    /**
     * Define query for findAllOrdersWithEgoods() method
     * 
     * @param \XLite\Model\Profile $profile Profile OPTIONAL
     *  
     * @return \XLite\Model\QuieryBuilder\AQueryBuilder
     */
    protected function defineFindAllOrdersWithEgoodsQuery(\XLite\Model\Profile $profile = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.items', 'item')
            ->innerJoin('item.privateAttachments', 'pa')
            ->leftJoin('o.orig_profile', 'op')
            ->orderBy('o.date', 'desc');

        if ($profile) {
            $qb->andWhere('op.profile_id = :opid')
                ->setParameter('opid', $profile->getProfileId());
        }

        return $qb;

    }
}

