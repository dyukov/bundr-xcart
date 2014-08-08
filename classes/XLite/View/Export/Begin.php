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

namespace XLite\View\Export;

/**
 * Begin section
 */
class Begin extends \XLite\View\AView
{
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'export/begin.tpl';
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getSections()
    {
        return array(
            'XLite\Logic\Export\Step\Products'   => 'Products',
            'XLite\Logic\Export\Step\Attributes' => 'Classes & Attributes',
            'XLite\Logic\Export\Step\AttributeValues\AttributeValueCheckbox' => 'Product attributes values',
            'XLite\Logic\Export\Step\Orders'     => 'Orders',
            'XLite\Logic\Export\Step\Categories' => 'Categories',
            'XLite\Logic\Export\Step\Users'      => 'Customers',
        );
    }

    /**
     * Check section is selected or not
     *
     * @param string $class Class
     *
     * @return boolean
     */
    protected function isSectionSelected($class)
    {
        return 'XLite\Logic\Export\Step\Products' == $class;
    }

    /**
     * Check section is disabled or not
     *
     * @param string $class Class
     *
     * @return boolean
     */
    protected function isSectionDisabled($class)
    {
        $found = false;

        $classes = array();

        $classes[] = $class;

        if ('XLite\Logic\Export\Step\AttributeValues\AttributeValueCheckbox' == $class) {
            $classes[] = 'XLite\Logic\Export\Step\AttributeValues\AttributeValueSelect';
            $classes[] = 'XLite\Logic\Export\Step\AttributeValues\AttributeValueText';
        }

        foreach ($classes as $c) {
            $class = new $c;
            if ($found = (0 < $class->count())) {
                break;
            }
        }

        return !$found;
    }
}
