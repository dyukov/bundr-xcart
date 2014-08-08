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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\View\Product\Details;

/**
 * Product attributes 
 */
abstract class AAttributes extends \XLite\View\AView
{
    protected $attributeList;

    /**
     * Widget param names
     */
    const PARAM_GROUP         = 'group';
    const PARAM_PRODUCT_CLASS = 'productClass';
    const PARAM_PERSONAL_ONLY = 'personalOnly';

    /**
     * Get step title
     *
     * @return string
     */
    public function getTitle()
    {
        if ($this->getAttributeGroup()) {
            $result = $this->getAttributeGroup()->getName();

        } elseif ($this->getProductClass()) {
            $result = static::t('{{name}} attributes', array('name' => $this->getProductClass()->getName()));

        } elseif ($this->getPersonalOnly()) {
            $result = static::t('Product-Specific attributes');

        } else {
            $result = static::t('Global attributes');
        }

        return $result;
    }

    /**
     * Get tooltip
     *
     * @return string
     */
    protected function getTooltip()
    {
        if ($this->getAttributeGroup()) {
            $result = '';

        } elseif ($this->getProductClass()) {
            $result = static::t(
                'These attributes can be applied to the "{{name}}" product class.',
                array(
                    'name' => func_htmlspecialchars($this->getProductClass()->getName()),
                )
            );

        } elseif ($this->getPersonalOnly()) {
            $result = static::t('These attributes can only be applied to this particular product.');

        } else {
            $result = static::t('These attributes can be applied to all the products in the store.');
        }

        return $result;
    }

    /**
     * Get list id
     *
     * @return integer
     */
    protected function getListId()
    {
        if ($this->getAttributeGroup()) {
            $result = $this->getAttributeGroup()->getId();

        } elseif ($this->getProductClass()) {
            $result = -2;

        } elseif ($this->getPersonalOnly()) {
            $result = -3;

        } else {
            $result = -1;
        }

        return $result;
    }

    /**
     * Get block style
     *
     * @return void
     */
    protected function getBlockStyle()
    {
        $style = $this->getAttributeGroup()
            ? 'attribute-group'
            : 'attributes';

        if (!$this->getAttributesList(true)) {
            $style .= ' empty';
        }

        return $style;
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
            static::PARAM_GROUP => new \XLite\Model\WidgetParam\Object(
                'Group', null, false, '\XLite\Model\AttributeGroup'
            ),
            static::PARAM_PRODUCT_CLASS => new \XLite\Model\WidgetParam\Object(
                'Product class', null, false, '\XLite\Model\ProductClass'
            ),
            static::PARAM_PERSONAL_ONLY  => new \XLite\Model\WidgetParam\Bool('Personal only', false),
        );
    }

    /**
     * Get attribute group
     *
     * @return \XLite\Model\AttributeGroup
     */
    protected function getAttributeGroup()
    {
        return $this->getParam(static::PARAM_GROUP);
    }

    /**
     * Get product class
     *
     * @return \XLite\Model\ProductClass
     */
    protected function getProductClass()
    {
        return $this->getParam(static::PARAM_PRODUCT_CLASS);
    }

    /**
     * Get personal only flag
     *
     * @return boolean
     */
    protected function getPersonalOnly()
    {
        return $this->getParam(static::PARAM_PERSONAL_ONLY);
    }

    /**
     * Get attributes list
     *
     * @param boolean $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    protected function getAttributesList($countOnly = false)
    {
        if (!isset($this->attributesList)) {
            $cnd = new \XLite\Core\CommonCell;
    
            if ($this->getPersonalOnly()) {
                $cnd->product = $this->getProduct();
    
            } elseif ($this->getAttributeGroup()) {
                $cnd->product = null;
                $cnd->attributeGroup = $this->getAttributeGroup();
    
            } else {
                $cnd->product = null;
                $cnd->attributeGroup = null;
                $cnd->productClass = $this->getProductClass();
            }
    
            $this->attributesList = $this->postprocessAttributes(
                \XLite\Core\Database::getRepo('\XLite\Model\Attribute')->search($cnd)
            );
        }

        return $countOnly
            ? count($this->attributesList)
            : $this->attributesList;
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
        return $attributes;
    }

    /**
     * Get attribute groups
     *
     * @return array
     */
    protected function getAttributeGroups()
    {
        return $this->getPersonalOnly() || $this->getAttributeGroup()
            ? array()
            : \XLite\Core\Database::getRepo('XLite\Model\AttributeGroup')->findByProductClass(
                $this->getProductClass()
              );
    }
}
