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
 * Attribute value (Checkbox)
 */
class Checkbox extends \XLite\View\Product\AttributeValue\Customer\ACustomer
{
    /**
     * Get dir
     *
     * @return string
     */
    protected function getDir()
    {
        return parent::getDir() . '/checkbox';
    }

    /**
     * Return values
     *
     * @return array
     */
    protected function getAttrValues()
    {

        $result = array();
        foreach ($this->getAttrValue() as $v) {
            $result[intval($v->getValue())] = $v;
        }

        ksort($result);

        return $result;
    }

    /**
     * Return title
     *
     * @return string
     */
    protected function getTitle()
    {
        return $this->getAttribute()->getName();
    }

    /**
     * Return modifier title
     *
     * @return string
     */
    protected function getModifierTitle()
    {
        $modifiers = array();
        foreach ($this->getAttrValues() as $k => $value) {
            foreach ($value::getModifiers() as $field => $v) {
                if (!isset($modifiers[$field])) {
                    $modifiers[$field] = 0;
                }
                $modifiers[$field] += (-1 + 2 * $k) * $value->getAbsoluteValue($field);
            }
        }

        foreach ($modifiers as $field => $modifier) {
            if (0 == $modifier) {
                unset($modifiers[$field]);

            } else {
                $modifiers[$field] = \XLite\Model\AttributeValue\AttributeValueSelect::formatModifier($modifier, $field);
            }
        }

        return $modifiers
            ? ' <span>(' . implode(', ', $modifiers) . ')</span>'
            : '';
    }

    /**
     * Return value is checked or not flag
     *
     * @return boolean
     */
    protected function isCheckedValue()
    {
        $res = false;

        $selectedIds = $this->getSelectedIds();
        $values = $this->getAttrValues();

        if (0 < count($values)) {
            if (0 < count($selectedIds)) {
                foreach ($values as $k => $v) {
                    $res = isset($selectedIds[$v->getAttribute()->getId()])
                        ? $selectedIds[$v->getAttribute()->getId()] == $v->getId() && $v->getValue()
                        : $res;

                    if ($res) {
                        break;
                    }
                }

            } else {
                foreach ($values as $k => $v) {
                    $res = $v->getValue() && $v->getDefaultValue();

                    if ($res) {
                        break;
                    }
                }
            }
        }

        return $res;
    }
}
