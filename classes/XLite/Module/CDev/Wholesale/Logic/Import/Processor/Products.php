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

namespace XLite\Module\CDev\Wholesale\Logic\Import\Processor;

/**
 * Products
 */
abstract class Products extends \XLite\Logic\Import\Processor\Products implements \XLite\Base\IDecorator
{
    // {{{ Columns

    /**
     * Define columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = parent::defineColumns();

        $columns['wholesalePrices'] = array(
            static::COLUMN_IS_MULTIPLE => true,
        );

        return $columns;
    }

    // }}}

    // {{{ Verification

    /**
     * Verify 'wholesalePrices' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyWholesalePrices($value, array $column)
    {
    }

    // }}}

    // {{{ Import

    /**
     * Import 'wholesalePrices' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param array                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importWholesalePricesColumn(\XLite\Model\Product $model, array $value, array $column)
    {
        foreach (\XLite\Core\Database::getRepo('\XLite\Module\CDev\Wholesale\Model\WholesalePrice')->findByProduct($model) as $price) {
            \XLite\Core\Database::getRepo('\XLite\Module\CDev\Wholesale\Model\WholesalePrice')->delete($price);
        }
        if ($value) {
            foreach ($value as $price) {
                if (preg_match('/^(\d+)(-(\d+))?(\((.+)\))?=(\d+\.?\d*)$/iSs', $price, $m)) {
                    $price = new \XLite\Module\CDev\Wholesale\Model\WholesalePrice();
                    $price->setMembership($this->normalizeValueAsMembership($m[5]));
                    $price->setProduct($model);
                    $price->setPrice($m[6]);
                    $price->setQuantityRangeBegin($m[1]);
                    $price->setQuantityRangeEnd(intval($m[3]));
                    \XLite\Core\Database::getEM()->persist($price);
                }
            }
        }
    }

    // }}}
}
