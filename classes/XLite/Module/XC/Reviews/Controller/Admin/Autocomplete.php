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

namespace XLite\Module\XC\Reviews\Controller\Admin;

/**
 * Autocomplete controller
 *
 */
abstract class Autocomplete extends \XLite\Controller\Admin\Autocomplete implements \XLite\Base\IDecorator
{

    /**
     * Assemble dictionary - conversation recipient
     *
     * @param string $term Term
     *
     * @return array
     */
    protected function assembleDictionaryReviewsUsersByEmail($term)
    {
        $profiles = \XLite\Core\Database::getRepo('\XLite\Model\Profile')->findSimilarByEmail($term);

        return $profiles ? $this->packProfileData($profiles) : array();
    }

    /**
     * Assemble dictionary - conversation recipient
     *
     * @param string $term Term
     *
     * @return array
     */
    protected function assembleDictionaryReviewsUsersByName($term)
    {
        $profiles = \XLite\Core\Database::getRepo('\XLite\Model\Profile')->findSimilarByName($term);

        return $profiles ? $this->packProfileData($profiles) : array();
    }

    /**
     * Get certain data from profile array for new array
     *
     * @param array $profiles Array of profiles
     *
     * @return array
     */
    protected function packProfileData(array $profiles)
    {
        $data = array();
        foreach ($profiles as $k => $profile) {
            $data[$profile->getProfileId()] = array(
                'email' => $profile->getLogin(),
                'name' => $profile->getName(),
            );
        }

        return $data;
    }

    /**
     * Assemble dictionary - conversation recipient
     *
     * @param string $term Term
     *
     * @return array
     */
    protected function assembleDictionaryReviewsProducts($term)
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{\XLite\Model\Repo\Product::P_SUBSTRING} = $term;
        $cnd->{\XLite\Model\Repo\Product::P_BY_SKU} = 'Y';
        $cnd->{\XLite\Model\Repo\Product::P_BY_TITLE} = 'Y';
        $cnd->{\XLite\Model\Repo\Product::P_BY_DESCR} = null;

        $products = \XLite\Core\Database::getRepo('\XLite\Model\Product')->search($cnd);

        return $products ? $this->packProductData($products) : array();
    }

    /**
     * Get certain data from products array for new array
     *
     * @param array $products Array of products
     *
     * @return array
     */
    protected function packProductData(array $products)
    {
        $data = array();
        foreach ($products as $k => $product) {
            $data[$product->getProductId()] = array(
                'value' => $product->getName(),
                'label' => '(' . $product->getSKU() . ') ' . $product->getName(),
            );
        }

        return $data;
    }
}
