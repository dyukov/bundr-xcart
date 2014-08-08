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

namespace XLite\View\Product\AttributeValue\Customer;

/**
 * Attribute value (Select)
 */
class Select extends \XLite\View\Product\AttributeValue\Customer\ACustomer
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
     * Return option title
     *
     * @param \XLite\Model\AttributeValue\AttributeValueSelect $value Value
     *
     * @return string
     */
    protected function getOptionTitle(\XLite\Model\AttributeValue\AttributeValueSelect $value)
    {
        return $value->asString();
    }

    /**
     * Return modifier title
     *
     * @param \XLite\Model\AttributeValue\AttributeValueSelect $value Value
     *
     * @return string
     */
    protected function getModifierTitle(\XLite\Model\AttributeValue\AttributeValueSelect $value)
    {
        $result = array();
        foreach ($value::getModifiers() as $field => $v) {
            $modifier = $value->getAbsoluteValue($field);
            if (0 != $modifier) {
                $result[] = \XLite\Model\AttributeValue\AttributeValueSelect::formatModifier($modifier, $field);
            }
        }

        return $result
            ? ' (' . implode(', ', $result) . ')'
            : '';
    }

    /**
     * Return value is selected or not flag
     *
     * @param \XLite\Model\AttributeValue\AttributeValueSelect $value Value
     *
     * @return boolean
     */
    protected function isSelectedValue(\XLite\Model\AttributeValue\AttributeValueSelect $value)
    {
        $selectedIds = $this->getSelectedIds();

        return isset($selectedIds[$value->getAttribute()->getId()])
            ? $selectedIds[$value->getAttribute()->getId()] == $value->getId()
            : $value->isDefault();
    }
}
