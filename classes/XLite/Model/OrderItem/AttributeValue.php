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

namespace XLite\Model\OrderItem;

/**
 * Attribute value
 *
 * @Entity
 * @Table  (name="order_item_attribute_values")
 */
class AttributeValue extends \XLite\Model\AEntity
{
    /**
     * ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="uinteger")
     */
    protected $id;

    /**
     * Order item (relation)
     *
     * @var \XLite\Model\OrderItem
     *
     * @ManyToOne  (targetEntity="XLite\Model\OrderItem", inversedBy="attributeValues")
     * @JoinColumn (name="item_id", referencedColumnName="item_id")
     */
    protected $orderItem;

    /**
     * Name
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $name;

    /**
     * Value
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $value;

    /**
     * Attribute id
     *
     * @var integer
     *
     * @Column (type="uinteger")
     */
    protected $attributeId = 0;

    /**
     * Attribute value id
     *
     * @var integer
     *
     * @Column (type="uinteger")
     */
    protected $attributeValueId = 0;

    /**
     * Attribute value (checkbox)
     *
     * @var \XLite\Model\AttributeValue\AttributeValueCheckbox
     *
     * @ManyToOne  (targetEntity="XLite\Model\AttributeValue\AttributeValueCheckbox")
     * @JoinColumn (name="attribute_value_checkbox_id", referencedColumnName="id")
     */
    protected $attributeValueC;

    /**
     * Attribute value (select)
     *
     * @var \XLite\Model\AttributeValue\AttributeValueSelect
     *
     * @ManyToOne  (targetEntity="XLite\Model\AttributeValue\AttributeValueSelect")
     * @JoinColumn (name="attribute_value_select_id", referencedColumnName="id")
     */
    protected $attributeValueS;

    /**
     * Attribute value (text)
     *
     * @var \XLite\Model\AttributeValue\AttributeValueText
     *
     * @ManyToOne  (targetEntity="XLite\Model\AttributeValue\AttributeValueText")
     * @JoinColumn (name="attribute_value_text_id", referencedColumnName="id")
     */
    protected $attributeValueT;

    /**
     * Set attribute value
     *
     * @var mixed $value Value
     *
     * @return void
     */
    public function setAttributeValue($value)
    {
        $method = 'setAttributeValue' . $value->getAttribute()->getType();
        if (method_exists($this, $method)) {
            $this->{$method}($value);
        }
    }

    /**
     * Get attribute value
     *
     * @return mixed
     */
    public function getAttributeValue()
    {
        if ($this->getAttributeValueS()) {
            $value = $this->getAttributeValueS();

        } elseif ($this->getAttributeValueC()) {
            $value = $this->getAttributeValueC();

        } elseif ($this->getAttributeValueT()) {
            $value = $this->getAttributeValueT();

        } else {
            $value = null;
        }

        return $value;
    }

    /**
     * Get order
     *
     * @return void
     */
    public function getOrder()
    {
        return $this->getOrderItem()->getOrder();
    }

    /**
     * Get actual selected attribute name
     *
     * @return string
     */
    public function getActualName()
    {
        return $this->getAttributeValue()
            ? $this->getAttributeValue()->getAttribute()->getName()
            : $this->getName();
    }

    /**
     * Get actual selected attribute value
     *
     * @return string
     */
    public function getActualValue()
    {
        $value = $this->getAttributeValue();

        return $value && !$value instanceOf \XLite\Model\AttributeValue\AttributeValueText
            ? $value->asString()
            : $this->getValue();
    }

    /**
     * Get actual attribute value
     *
     * @return mixed
     */
    public function getActualAttributeValue()
    {
        $attributeValue = $this->getAttributeValue();

        if (
            !$attributeValue
            && $this->getAttributeId()
            && $this->getAttributeValueId()
        ) {
            $attribute = \XLite\Core\Database::getRepo('\XLite\Model\Attribute')->find($this->getAttributeId());
            if ($attribute) {
                $class = $attribute::getAttributeValueClass($attribute->getType());
                $attributeValue = \XLite\Core\Database::getRepo($class)->find(
                    $this->getAttributeValueId()
                );
            }
        }

        return $attributeValue;
    }
}
