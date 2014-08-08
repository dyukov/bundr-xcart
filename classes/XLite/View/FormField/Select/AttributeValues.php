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

namespace XLite\View\FormField\Select;

/**
 * Attribute groups selector
 */
class AttributeValues extends \XLite\View\FormField\Select\Regular
{
    /**
     * Common params
     */
    const PARAM_ATTRIBUTE  = 'attribute';
    const PARAM_PRODUCT    = 'product';

    /**
     * Get options
     *
     * @return array
     */
    protected function getOptions()
    {
        $list = parent::getOptions();

        if (!$list) {
            $attribute = $this->getAttribute();
            $product = $this->getProduct();

            if ($attribute) {
                if ($product) {
                    foreach ($attribute->getAttributeValue($product) as $attributeValue) {
                        $list[$attributeValue->getId()] = $attributeValue->asString();
                    }

                } elseif ($attribute::TYPE_CHECKBOX == $attribute->getType()) {
                    $list[1] = static::t('Yes');
                    $list[0] = static::t('No');

                } elseif ($attribute::TYPE_SELECT == $attribute->getType()) {
                    foreach ($attribute->getAttributeOptions() as $v) {
                        $list[$v->getId()] = $v->getName();
                    }
                }
            }
        }

        return $list;
    }

    /**
     * Get default options
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array();
    }

    /**
     * Define widget params
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_ATTRIBUTE => new \XLite\Model\WidgetParam\Object(
                'Attribute', null, false, 'XLite\Model\Attribute'
            ),
            self::PARAM_PRODUCT => new \XLite\Model\WidgetParam\Object(
                'Product', null, false, 'XLite\Model\Product'
            ),
        );
    }

    /**
     * Get attribute
     *
     * @return \XLite\Model\Attribute
     */
    public function getAttribute() {
        return $this->getParam(self::PARAM_ATTRIBUTE);
    }

    /**
     * Get product
     *
     * @return \XLite\Model\Product
     */
    public function getProduct() {
        return $this->getParam(self::PARAM_PRODUCT);
    }
}
