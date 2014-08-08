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

namespace XLite\View\Product\AttributeValue;

/**
 * Abstract attribute value
 */
abstract class AAttributeValue extends \XLite\View\Product\AProduct
{
    /**
     * Common params
     */
    const PARAM_ATTRIBUTE  = 'attribute';

    /**
     * Attribute value
     *
     * @var mixed
     */
    protected $attributeValue;

    /**
     * Return field attribute
     *
     * @return \XLite\Model\Attribute
     */
    protected function getAttribute()
    {
        return $this->getParam(self::PARAM_ATTRIBUTE);
    }

    /**
     * Return field value
     *
     * @return mixed
     */
    protected function getAttrValue()
    {
        if (
            !isset($this->attributeValue)
            && $this->getAttribute()
        ) {
            $this->attributeValue = $this->getAttribute()->getAttributeValue($this->getProduct());
        }

        return $this->attributeValue;
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
            static::PARAM_ATTRIBUTE => new \XLite\Model\WidgetParam\Object(
                'Attribute', null, false, 'XLite\Model\Attribute'
            ),
        );
    }

    /**
     * Is multiple flag
     *
     * @return boolean
     */
    protected function isMultiple()
    {
        return $this->getAttribute()
            && $this->getAttribute()->isMultiple($this->getProduct());
    }

    /**
     * Get dir
     *
     * @return string
     */
    protected function getDir()
    {
        return parent::getDir() . '/attribute_value';
    }

    /**
     * Get style
     *
     * @return string
     */
    protected function getStyle()
    {
        return 'attribute-value type-'
            . strtolower($this->getAttributeType())
            . ($this->isMultiple() ? ' multiple' : '');
    }

    /**
     * Return modifiers
     *
     * @return array
     */
    protected function getModifiers()
    {
        return \XLite\Model\AttributeValue\Multiple::getModifiers();
    }

    /**
     * Get style
     *
     * @param mixed  $attributeValue Aattribute value
     * @param string $field          Field
     *
     * @return string
     */
    protected function getModifierValue($attributeValue, $field)
    {
        return $attributeValue
            ? $attributeValue->getModifier($field)
            : '';
    }

    /**
     * Is default flag
     *
     * @param mixed $attributeValue Aattribute value
     *
     * @return boolean
     */
    protected function isDefault($attributeValue) {
        return $attributeValue
            && is_object($attributeValue)
            && $attributeValue->getDefaultValue();
    }
}
