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
 * Abstract attribute value (admin)
 */
abstract class AAdmin extends \XLite\View\Product\AttributeValue\AAttributeValue
{
    /**
     * Get attribute type
     *
     * @return string
     */
    abstract protected function getAttributeType();

    /**
     * Return field name
     *
     * @param string $field Field OPTIONAL
     * @param string $id    Id OPTIONAL
     *
     * @return string
     */
    protected function getName($field = 'value', $id = null)
    {
        $name = $this->getAttribute()
            ? 'attributeValue[' . $this->getAttribute()->getId() . ']'
            : 'newValue[NEW_ID]';

        $name .= '[' . $field . ']';

        if (isset($id)) {
            $name .= '[' . $id . ']';
        }

        return $name;
    }

    /**
     * Get modifiers as string
     *
     * @param mixed $attributeValue Aattribute value
     *
     * @return string
     */
    protected function getModifiersAsString($attributeValue)
    {
        $result = '';

        if ($attributeValue) {
            foreach ($this->getModifiers() as $field => $modifier) {
                $str = $this->getModifierValue($attributeValue, $field);
                if ($str) {
                    $result .= ' <span class="' . $field . '-modifier">' . trim($str) . '</span>';
                }
            }
            if ($this->isDefault($attributeValue)) {
                $result = static::t('Default') . ($result ? ', ' : '') . $result;
            }
        }

        return $result;
    }

    /**
     * Check attribute is modified or not
     *
     * @return boolean
     */
    protected function isModified()
    {
        return true;
    }

    /**
     * Get multiple title
     *
     * @return string
     */
    protected function getMultipleTitle()
    {
        return static::t('multi value');
    }
}
