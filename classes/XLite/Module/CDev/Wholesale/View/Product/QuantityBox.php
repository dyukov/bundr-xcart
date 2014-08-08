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

namespace XLite\Module\CDev\Wholesale\View\Product;

/**
 * Quantity box
 */
class QuantityBox extends \XLite\View\Product\QuantityBox implements \XLite\Base\IDecorator
{
    /**
     * Return minimum quantity
     *
     * @return integer
     */
    protected function getMinQuantity()
    {
        $minQuantity = $this->getProduct()->getMinQuantity(
            \XLite\Core\Auth::getInstance()->getProfile() ? \XLite\Core\Auth::getInstance()->getProfile()->getMembership() : null
        );

        $result = parent::getMinQuantity();

        $minimumQuantity = $minQuantity ? $minQuantity : $result;

        if (!$this->isCartPage()) {

            $items = \XLite\Model\Cart::getInstance()->getItemsByProductId($this->getProduct()->getProductId());

            $quantityInCart = $items
                ? \Includes\Utils\ArrayManager::sumObjectsArrayFieldValues(
                    $items,
                    'getAmount',
                    true
                )
                : 0;

            $result = ($minimumQuantity > $quantityInCart) ? ($minimumQuantity - $quantityInCart) : $result;

        } else {

            $result = $minimumQuantity;
        }

        return $result;
    }
}
