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

namespace XLite\Module\CDev\Wholesale\View;

/**
 * Wholesale prices for product
 */
class ProductPrice extends \XLite\View\AView
{
    /**
     * Widget parameter names
     */
    const PARAM_PRODUCT = 'product';


    /**
     * Cache for wholesale prices array
     *
     * @var   array
     */
    protected $wholesalePrices = null;


    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/CDev/Wholesale/product_price/style.css';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/Wholesale/product_price/body.tpl';
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_PRODUCT => new \XLite\Model\WidgetParam\Object(
                'Product',
                $this->getProduct(),
                false,
                '\XLite\Model\Product'
            ),
        );
    }

    /**
     * Check widget visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && $this->checkWholesalePrices();
    }

    /**
     * Check if wholesale prices should be displayed on product page
     *
     * @return boolean
     */
    protected function checkWholesalePrices()
    {
        $result = false;

        if ($this->getProduct()->isWholesalePricesEnabled()) {

            $prices = $this->getWholesalePrices();

            // Display always if count of prices more than 1
            $result = (1 < count($prices));

            if (!$result && 0 < count($prices)) {
                // Do not display if found a single price with qty range started from 1
                $price = array_shift($prices);
                $result = 1 < $price->getQuantityRangeBegin();
            }
        }

        return $result;
    }

    /**
     * Return wholesale prices for the current product
     *
     * @return \Doctrine\ORM\PersistentCollection
     */
    protected function getWholesalePrices()
    {
        if (!isset($this->wholesalePrices)) {

            $membership = \XLite\Core\Auth::getInstance()->getProfile()
                ? \XLite\Core\Auth::getInstance()->getProfile()->getMembership()
                : null;

            $this->wholesalePrices = \XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\WholesalePrice')->getWholesalePrices(
                $this->getParam(self::PARAM_PRODUCT),
                $membership
            );
        }

        return $this->wholesalePrices;
    }
}
