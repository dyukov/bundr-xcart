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

namespace XLite\View\ItemsList\Product\Customer\Category;

/**
 * Category products list widget
 *
 *
 * @ListChild (list="center.bottom", zone="customer", weight="200")
 */
class Main extends \XLite\View\ItemsList\Product\Customer\Category\ACategory
{
    /**
     * Cache allowed
     *
     * @param string $template Template
     *
     * @return boolean
     */
    protected function isCacheAllowed($template)
    {
        return parent::isCacheAllowed($template)
            && !isset($template)
            && $this->isStaticProductList();
    }

    /**
     * Cache availability
     *
     * @return boolean
     */
    protected function isCacheAvailable()
    {
        // We use cache only if there are no products in the cart
        return true;
    }

    /**
     * Get cache TTL (seconds)
     *
     * @return integer
     */
    protected function getCacheTTL()
    {
        return 3600;
    }

    /**
     * Defines if the widget is listening to #hash changes
     * 
     * @return boolean
     */
    protected function getListenToHash()
    {
        return true;
    }
    
    /**
     * Defines the #hash prefix of the data for the widget
     * @TODO implement!
     * 
     * @return string
     */
    protected function getListenToHashPrefix()
    {
        return 'product.category';
    }

}
