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

namespace XLite\Module\CDev\PINCodes\Model\Repo;

/**
 * PinCode repository
 *
 */
class PinCode extends \XLite\Model\Repo\ARepo
{

    /**
     * Common search
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Count only OPTIONAL
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function search(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $queryBuilder = $this->createQueryBuilder('p');
        if ($cnd->limit) {
            $queryBuilder->setFirstResult($cnd->limit[0]);
            $queryBuilder->setMaxResults($cnd->limit[1]);
        }

        if ($cnd->product) {
            $queryBuilder->andWhere('p.product=:product')->setParameter('product', $cnd->product);
        }

        return $countOnly ? count($queryBuilder->getResult()) : $queryBuilder->getResult();
    }

    /**
     * Counts sold pin codes by product
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return integer
     */
    public function countSold(\XLite\Model\Product $product)
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->andWhere('p.product=:product')
            ->andWhere('p.isSold=1')
            ->setParameter('product', $product);

        return count($queryBuilder->getResult());
    }

    /**
     * Counts sold pin codes by product
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return integer
     */
    public function countRemaining(\XLite\Model\Product $product)
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->andWhere('p.product=:product')
            ->andWhere('p.isSold=0')
            ->setParameter('product', $product);

        return count($queryBuilder->getResult());
    }

    /**
     * Returns not sold pin code 
     *
     * @param \XLite\Model\Product $product Product
     * @param integer              $index   Index
     *
     * @return \XLite\Module\CDev\PINCodes\Model\PinCode
     */
    public function getAvailablePinCode(\XLite\Model\Product $product, $index)
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->andWhere('p.product=:product')
            ->andWhere('p.isSold=0')
            ->setParameter('product', $product)
            ->setFirstResult($index)
            ->setMaxResults(1);

        return $queryBuilder->getSingleResult();
    }
}
