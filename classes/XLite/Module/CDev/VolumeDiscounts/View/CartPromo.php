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

namespace XLite\Module\CDev\VolumeDiscounts\View;

/**
 * Volume discounts promotion block widget in the cart
 *
 * @ListChild (list="cart.panel.totals", weight="100")
 */
class CartPromo extends \XLite\View\AView
{
    /**
     * nextDiscount 
     * 
     * @var   \XLite\Module\CDev\VolumeDiscount\Model\VolumeDiscount
     */
    protected $nextDiscount = null;

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/CDev/VolumeDiscounts/cart.css';

        return $list;
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/VolumeDiscounts/cart_promo.tpl';
    }

    /**
     * Get current discount rate applied to cart
     * 
     * @return \XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount
     */
    protected function getCurrentDiscount()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount')
            ->getFirstDiscountBySubtotal(
                $this->getCart()->getSubtotal(),
                $this->getCart()->getProfile() ? $this->getCart()->getProfile()->getMembership() : null
            );
    }

    /**
     * Get next discount rate available for cart subtotal
     * 
     * @return \XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount
     */
    protected function getNextDiscount()
    {
        if (!isset($this->nextDiscount)) {
            $this->nextDiscount = \XLite\Core\Database::getRepo('XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount')
                ->getNextDiscount(
                    $this->getCart()->getSubtotal(),
                    $this->getCart()->getProfile() ? $this->getCart()->getProfile()->getMembership() : null
                );
        }

        return $this->nextDiscount;
    }

    /**
     * Returns true if next discount rate is available for cart
     * 
     * @return boolean
     */
    protected function hasNextDiscount()
    {
        if (!isset($this->nextDiscount)) {
            $this->nextDiscount = $this->getNextDiscount();

            if (isset($this->nextDiscount)) {
                $nextValue = $this->getCart()->getCurrency()->formatValue(
                    $this->nextDiscount->getAmount($this->getCart())
                );

                $currentValue = 0;

                if (0 < $nextValue) {
                    $currentDiscount = $this->getCurrentDiscount();

                    if (isset($currentDiscount)) {
                        $currentValue = $this->getCart()->getCurrency()->formatValue(
                            $currentDiscount->getAmount($this->getCart())
                        );
                    }
                }

                if ($nextValue <= $currentValue) {
                    $this->nextDiscount = null;
                }
            }
        }

        return isset($this->nextDiscount);
    }

    /**
     * Get formatted next discount subtotal 
     * 
     * @return string
     */
    protected function getNextDiscountSubtotal()
    {
        $result = '';

        $discount = $this->getNextDiscount();

        if (isset($discount)) {
            $result = $this->formatPrice($discount->getSubtotalRangeBegin(), $this->getCart()->getCurrency());
        }

        return $result;
    }

    /**
     * Get formatted next discount value 
     * 
     * @return string
     */
    protected function getNextDiscountValue()
    {
        $result = '';

        $discount = $this->getNextDiscount();

        if (isset($discount)) {

            if ($discount->isAbsolute()) {
                $result = $this->formatPrice($discount->getValue(), $this->getCart()->getCurrency());

            } else {
                $str = sprintf('%0.f', $discount->getValue());
                $precision = strlen(sprintf('%d', intval(substr($str, strpos($str, '.') + 1))));
                $result = round($discount->getValue(), $precision) . '%';
            }
        }

        return $result;
    }
}
