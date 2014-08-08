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

namespace XLite\Module\XC\Reviews\Model\Repo;

/**
 * The Profile model repository
 */
abstract class Profile extends \XLite\Model\Repo\Profile implements \XLite\Base\IDecorator
{
    /**
     * Search profile by email
     *
     * @param string $email User's email
     *
     * @return array
     */
    public function findSimilarByEmail($email)
    {
        return $this->defineSimilarByEmailQuery($email)->getResult();
    }

    /**
     * Search profile by customer name
     *
     * @param string $name User's customer name
     *
     * @return array
     */
    public function findSimilarByName($name)
    {
        return $this->defineSimilarByNameQuery($name)->getResult();
    }

    /**
     * Create query builder to search profile by email
     *
     * @param string $email User's email
     *
     * @return string
     */
    protected function defineSimilarByEmailQuery($email)
    {
        return $this->createQueryBuilder()
            ->andWhere('p.login LIKE :email')
            ->andWhere('p.order IS NULL')
            ->setParameter('email', '%' . $email . '%');
    }

    /**
     * Create query builder to search profile by customer name
     *
     * @param string $name User's customer name
     *
     * @return string
     */
    protected function defineSimilarByNameQuery($name)
    {
        $parts = explode(' ', $name, 2);
        if (empty($parts[1])) {
            unset($parts[1]);
        }

        $qb = $this->createQueryBuilder()
            ->linkInner('p.addresses')
            ->linkInner('addresses.addressFields')
            ->linkInner('addressFields.addressField')
            ->andWhere('p.order IS NULL');

        $conditions = array(
            '(addressField.serviceName = \'firstname\' AND addressFields.value LIKE :firstname)',
            '(addressField.serviceName = \'lastname\' AND addressFields.value LIKE :lastname)',
        );

        if (count($parts) == 1) {
            $qb->andWhere(implode(' OR ', $conditions))
                ->setParameter('firstname', '%' . $parts[0] . '%')
                ->setParameter('lastname', '%' . $parts[0] . '%');
        } else {
            $qb->andWhere(implode(' OR ', $conditions))
                ->setParameter('firstname', '%' . $parts[0] . '%')
                ->setParameter('lastname', '%' . $parts[1] . '%');
        }

        return $qb;
    }
}
