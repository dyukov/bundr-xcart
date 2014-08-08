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
 * Attribute value (Checkbox)
 */
class Checkbox extends \XLite\View\Product\AttributeValue\Admin\AAdmin
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
     * Get attribute type
     *
     * @return string
     */
    protected function getAttributeType()
    {
        return \XLite\Model\Attribute::TYPE_CHECKBOX;
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
                $result[intval($v->getValue())] = $v;
            }
            unset($values);
        }

        foreach (array(0, 1) as $v) {
            if (!isset($result[$v])) {
                $result[$v] = null;
            }
        }

        ksort($result);

        return $result;
    }

    /**
     * Return select value
     *
     * @return boolean
     */
    protected function getSelectValue()
    {
        $value = $this->getAttrValue();
        if (is_array($value)) {
            foreach ($value as $v) {
                if ($v) {
                    $value = $v;
                    break;
                }
            }
        }

        return is_object($value)
            ? $value->getValue()
            : $value;
    }

    /**
     * Return label
     *
     * @var indegeer $id Id
     *
     * @return string
     */
    protected function getLabel($id)
    {
        return static::t($id ? 'Yes' : 'No');
    }
}
