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

namespace XLite\View\Product\AttributeValue\Admin;

/**
 * Attribute value (Select)
 */
class Select extends \XLite\View\Product\AttributeValue\Admin\AAdmin
{
    /**
     * Get dir
     *
     * @return string
     */
    protected function getDir()
    {
        return parent::getDir() . '/select';
    }

    /**
     * Return values
     *
     * @return array
     */
    protected function getAttrValues()
    {
        $values = $this->getAttrValue();

        if ($values) {
            $result = array();
            foreach ($values as $v) {
                $result[$v->getId()] = $v;
            }
            unset($values);

        } else {
            $result = array(null);
        }
        $result['NEW_ID'] = null;

        return $result;
    }

    /**
     * Get attribute type
     *
     * @return string
     */
    protected function getAttributeType()
    {
        return \XLite\Model\Attribute::TYPE_SELECT;
    }

    /**
     * Return name of widget class
     *
     * @return string
     */
    protected function getWidgetClass()
    {
        return $this->getAttribute() && !$this->getAttribute()->getProduct()
            ? '\XLite\View\FormField\Input\Text\AttributeOption'
            : '\XLite\View\FormField\Input\Text';
    }

    /**
     * Return field value
     *
     * @param \XLite\Model\AttributeValue\AttributeValueSelect $attributeValue Attribute value
     *
     * @return mixed
     */
    protected function getFieldValue($attributeValue)
    {
        return $attributeValue && $this->getAttribute() && $this->getAttribute()->getProduct()
            ? $attributeValue->getAttributeOption()->getName()
            : $attributeValue;
    }

    /**
     * Get value style
     *
     * @param $id Id
     *
     * @return string
     */
    protected function getValueStyle($id)
    {
        return 'line clearfix '
            . (is_int($id) ? 'value' : 'new');
    }
}
