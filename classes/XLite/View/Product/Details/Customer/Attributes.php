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
 * Product attributes
 */
class Attributes extends \XLite\View\Product\Details\AAttributes
{
    /**
     * Attributes
     *
     * @var array
     */
    protected $attributes;

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getAttrList()
    {
        if (!isset($this->attributes)) {
            $this->attributes = array();
            foreach ($this->getAttributesList() as $a) {
                $value = $a->getAttributeValue($this->getProduct(), true);
                if (is_array($value)) {
                    $value = implode($a::DELIMITER, $value);
                }
                if (
                    $value
                    && (
                        $a::TYPE_CHECKBOX != $a->getType()
                        || static::t('No') != $value
                    )
                ) {
                    $this->attributes[] = array(
                        'name'  => $a->getName(),
                        'value' => htmlspecialchars($value),
                        'class' => $this->getFieldClass($a, $value)
                    );
                }
            }
        }

        return $this->attributes;
    }

    /**
     * Return field class
     *
     * @param \XLite\Model\Attribute $attribute Attribute
     * @param string                 $value     Value
     *
     * @return string
     */
    protected function getFieldClass(\XLite\Model\Attribute $attribute, $value)
    {
        $class = str_replace(' ', '-', strtolower($attribute->getTypes($attribute->getType())));
        if (\XLite\Model\Attribute::TYPE_CHECKBOX == $attribute->getType()) {
            $class .= ' ' . (static::t('Yes') == $value ? 'checked' : 'no-checked');
        }

        return $class;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'product/details/parts/attribute.tpl';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getAttrList();
    }

    /**
     * Postprocess attributes
     *
     * @param array $attributes Attributes
     *
     * @return array
     */
    protected function postprocessAttributes(array $attributes)
    {
        $attributes = parent::postprocessAttributes($attributes);

        $product = $this->getProduct();
        if ($product && !\XLite::isAdminZone()) {
            foreach ($attributes as $i => $attribute) {
                $value = $attribute->getAttributeValue($product);
                if (
                    $value
                    && $value instanceOf \XLite\Model\AttributeValue\AttributeValueText
                    && $value->getEditable()
                ) {
                    unset($attributes[$i]);
                }
            }
            $attributes = array_values($attributes);
        }

        return $attributes;
    }

}
