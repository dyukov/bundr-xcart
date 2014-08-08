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

namespace XLite\View\Product\Details\Customer;

/**
 * Product widget
 */
abstract class Widget extends \XLite\View\Product\AProduct
{
    /**
     * Widget parameters
     */
    const PARAM_PRODUCT          = 'product';
    const PARAM_PRODUCT_ID       = 'product_id';
    const PARAM_ATTRIBUTE_VALUES = 'attribute_values';

    /**
     * Product model cache
     *
     * @var \XLite\Model\Product | null
     */
    protected $product;

    /**
     * Return the specific widget service name to make it visible as specific CSS class
     *
     * @return null|string
     */
    abstract public function getFingerprint();

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_PRODUCT          => new \XLite\Model\WidgetParam\Object('Product', null, false, '\XLite\Model\Product'),
            static::PARAM_PRODUCT_ID       => new \XLite\Model\WidgetParam\Int('Product ID', 0),
            static::PARAM_ATTRIBUTE_VALUES => new \XLite\Model\WidgetParam\String('Attribute values IDs', ''),
        );
    }

    /**
     * Get product
     *
     * @return \XLite\Model\Product
     */
    protected function getProduct()
    {
        if (is_null($this->product)) {
            $this->product = $this->getParam(self::PARAM_PRODUCT_ID)
                ? \XLite\Core\Database::getRepo('XLite\Model\Product')->find($this->getParam(self::PARAM_PRODUCT_ID))
                : $this->getParam(self::PARAM_PRODUCT);
        }

        return $this->product;
    }

    /**
     * Return product attributes array from the request parameters
     *
     * @return array
     */
    protected function getAttributeValues()
    {
        $ids = array();
        $attributeValues = trim($this->getParam(static::PARAM_ATTRIBUTE_VALUES), ',');

        if ($attributeValues) {
            $attributeValues = explode(',', $attributeValues);
            foreach ($attributeValues as $v) {
                $v = explode('_', $v);
                $ids[$v[0]] = $v[1];
            }
        }

        return $this->getProduct()->prepareAttributeValues($ids);
    }

    /**
     * Check widget visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && $this->getProduct();
    }
}
