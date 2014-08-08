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

namespace XLite\Model;

/**
 * Attribute
 *
 * @Entity
 * @Table  (name="attributes")
 */
class Attribute extends \XLite\Model\Base\I18n
{
    /*
     * Attribute types
     */
    const TYPE_TEXT     = 'T';
    const TYPE_CHECKBOX = 'C';
    const TYPE_SELECT   = 'S';

    /*
     * Add to new products or class’s assigns automatically with select value
     */
    const ADD_TO_NEW_YES    = 'Y'; // 'Yes'
    const ADD_TO_NEW_NO     = 'N'; // 'NO'
    const ADD_TO_NEW_YES_NO = 'B'; // 'YES/NO' (BOTH)

    /*
     * Attribute delimiter
     */
    const DELIMITER = ', ';

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
     * Position
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $position = 0;

    /**
     * Decimals
     *
     * @var integer
     *
     * @Column (type="integer", length=1)
     */
    protected $decimals = 0;

    /**
     * Product class
     *
     * @var \XLite\Model\ProductClass
     *
     * @ManyToOne  (targetEntity="XLite\Model\ProductClass", inversedBy="attributes")
     * @JoinColumn (name="product_class_id", referencedColumnName="id")
     */
    protected $productClass;

    /**
     * Attribute group
     *
     * @var \XLite\Model\AttributeGroup
     *
     * @ManyToOne  (targetEntity="XLite\Model\AttributeGroup", inversedBy="attributes")
     * @JoinColumn (name="attribute_group_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $attributeGroup;

    /**
     * Attribute options
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\AttributeOption", mappedBy="attribute", cascade={"all"})
     */
    protected $attribute_options;

    /**
     * Product
     *
     * @var \XLite\Model\Product
     *
     * @ManyToOne  (targetEntity="XLite\Model\Product", inversedBy="attributes")
     * @JoinColumn (name="product_id", referencedColumnName="product_id")
     */
    protected $product;

    /**
     * Option type
     *
     * @var string
     *
     * @Column (type="fixedstring", length=1)
     */
    protected $type = self::TYPE_SELECT;

    /**
     * Add to new products or class’s assigns automatically
     *
     * @var boolean
     *
     * @Column (type="fixedstring", length=1)
     */
    protected $addToNew = '';

    /**
     * Attribute properties
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\AttributeProperty", mappedBy="attribute")
     */
    protected $attribute_properties;

    /**
     * Return name of widget class
     *
     * @param string $type Type
     *
     * @return string
     */
    static public function getWidgetClass($type)
    {
        return '\XLite\View\Product\AttributeValue\\'
            . (\XLite::isAdminZone() ? 'Admin' : 'Customer')
            . '\\'
            . static::getTypes($type, true);
    }

    /**
     * Return name of value class
     *
     * @param string $type Type
     *
     * @return string
     */
    static public function getAttributeValueClass($type)
    {
        return '\XLite\Model\AttributeValue\AttributeValue'
            . static::getTypes($type, true);
    }

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->attribute_options = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Return number of products associated with this attribute
     *
     * @return integer
     */
    public function getProductsCount()
    {
        return $this->getClass()->getProductsCount();
    }

    /**
     * Return list of types or type
     *
     * @param string  $type              Type OPTIONAL
     * @param boolean $returnServiceType Return service type OPTIONAL
     *
     * @return array | string
     */
    public static function getTypes($type = null, $returnServiceType = false)
    {
        $list = array(
            self::TYPE_SELECT   => static::t('Plain field'),
            self::TYPE_TEXT     => static::t('Textarea'),
            self::TYPE_CHECKBOX => static::t('Yes/No'),
        );

        $listServiceTypes = array(
            self::TYPE_SELECT   => 'Select',
            self::TYPE_TEXT     => 'Text',
            self::TYPE_CHECKBOX => 'Checkbox',
        );

        $list = $returnServiceType ? $listServiceTypes : $list;

        return isset($type)
            ? (isset($list[$type]) ? $list[$type] : null)
            : $list;
    }

    /**
     * Return list of 'addToNew' types
     *
     * @return array
     */
    public static function getAddToNewTypes()
    {
        return array(
            self::ADD_TO_NEW_YES,
            self::ADD_TO_NEW_NO,
            self::ADD_TO_NEW_YES_NO,
        );
    }

    /**
     * Return values associated with this attribute
     *
     * @return mixed
     */
    public function getAttributeValues()
    {
        $cnd = new \XLite\Core\CommonCell;
        $cnd->attribute = $this;

        return \XLite\Core\Database::getRepo($this->getAttributeValueClass($this->getType()))
            ->search($cnd);
    }

    /**
     * Return number of values associated with this attribute
     *
     * @return integer
     */
    public function getAttributeValuesCount()
    {
        $cnd = new \XLite\Core\CommonCell;
        $cnd->attribute = $this;

        return \XLite\Core\Database::getRepo($this->getAttributeValueClass($this->getType()))
            ->search($cnd, true);
    }

    /**
     * Set 'addToNew' value
     *
     * @param string $value Value
     *
     * @return void
     */
    public function setAddToNew($value)
    {
        if (
            self::TYPE_CHECKBOX == $this->getType()
            && is_array($value)
        ) {
            if (2 == count($value)) {
                $value = self::ADD_TO_NEW_YES_NO;

            } elseif (1 == count($value)) {
                $value = array_shift($value) ? self::ADD_TO_NEW_YES : self::ADD_TO_NEW_NO;
            }
        }

        $this->addToNew = in_array($value, static::getAddToNewTypes()) ? $value : '';
    }

    /**
     * Get 'addToNew' value
     *
     * @param string $value Value
     *
     * @return mixed
     */
    public function getAddToNew()
    {
        $value = null;
        if (self::TYPE_CHECKBOX == $this->getType()) {
            switch ($this->addToNew) {
                case self::ADD_TO_NEW_YES:
                    $value = array(1);
                    break;

                case self::ADD_TO_NEW_NO:
                    $value = array(0);
                    break;

                case self::ADD_TO_NEW_YES_NO:
                    $value = array(0, 1);
                    break;

                default:
            }
        }

        return $value;
    }

    /**
     * Set type
     *
     * @param string $type Type
     *
     * @return void
     */
    public function setType($type)
    {
        $types = static::getTypes();

        if (isset($types[$type])) {
            if (
                $this->type
                && $type != $this->type
                && $this->getId()
            ) {
                foreach ($this->getAttributeOptions() as $option) {
                    \XLite\Core\Database::getEM()->remove($option);
                }
                foreach ($this->getAttributeValues() as $value) {
                    \XLite\Core\Database::getEM()->remove($value);
                }
            }
            $this->type = $type;
        }
    }

    /**
     * Return property
     *
     * @param \XLite\Model\Product $product Product OPTIONAL
     *
     * @return \XLite\Model\AttributeProperty
     */
    protected function getProperty($product)
    {
        $result = \XLite\Core\Database::getRepo('XLite\Model\AttributeProperty')->findOneBy(
            array(
                'attribute' => $this,
                'product'   => $product,
            )
        );

        if (!$result) {
            $result = new \XLite\Model\AttributeProperty();
            $result->setAttribute($this);
            $result->setProduct($product);
            $this->addAttributeProperty($result);
        }

        return $result;
    }

    /**
     * Returns position
     *
     * @param \XLite\Model\Product $product Product OPTIONAL
     *
     * @return integer
     */
    public function getPosition($product = null)
    {
        if ($product) {
            $result = $this->getProperty($product);
            $result = $result ? $result->getPosition() : 0;

        } else {
            $result = $this->position;
        }

        return $result;
    }

    /**
     * Set the position
     *
     * @param intger|array $value
     *
     * @return void
     */
    public function setPosition($value)
    {
        if (is_array($value)) {
            $property = $this->getProperty($value['product']);
            $property->setPosition($value['position']);
            \XLite\Core\Database::getEM()->persist($property);

        } else {
            $this->position = $value;
        }
    }

    /**
     * Add to new product
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return void
     */
    public function addToNewProduct(\XLite\Model\Product $product)
    {
        $class = $this->getAttributeValueClass($this->getType());
        $repo = \XLite\Core\Database::getRepo($class);

        if ($this->getAddToNew()) {
            foreach ($this->getAddToNew() as $value) {
                $this->createAttributeValue($product)->setValue($value);
            }

        } elseif (self::TYPE_SELECT == $this->getType()) {
            foreach (
                \XLite\Core\Database::getRepo('XLite\Model\AttributeOption')->findBy(
                    array(
                        'attribute' => $this,
                        'addToNew'  => true,
                    )
                ) as $attributeOption
            ) {
                $this->createAttributeValue($product)->setAttributeOption($attributeOption);
            }
        }
    }

    /**
     * Apply changes
     *
     * @param \XLite\Model\Product $product Product
     * @param mixed                $changes Changes
     *
     * @return void
     */
    public function applyChanges(\XLite\Model\Product $product, $changes)
    {
        if (
            (
                !$this->getProductClass()
                && !$this->getProduct()
            )
            || (
                $this->getProductClass()
                && $product->getProductClass()
                && $this->getProductClass()->getId() == $product->getProductClass()->getId()
            )
            || (
                $this->getProduct()
                && $this->getProduct()->getId() == $product->getId()
            )
        ) {
            $class = $this->getAttributeValueClass($this->getType());
            $repo = \XLite\Core\Database::getRepo($class);

            switch ($this->getType()) {
                case self::TYPE_TEXT:
                    $this->setAttributeValue($product, $changes);
                    break;

                case self::TYPE_CHECKBOX:
                case self::TYPE_SELECT:
                    foreach ($repo->findBy(array('product' => $product, 'attribute' => $this)) as $av) {
                        $uniq = self::TYPE_CHECKBOX == $this->getType()
                            ? $av->getValue()
                            : $av->getAttributeOption()->getId();

                        if (in_array($uniq, $changes['deleted'])) {
                            $repo->delete($av);

                        } else if (
                            isset($changes['changed'][$uniq])
                            || isset($changes['added'][$uniq])
                        ) {
                            $data = isset($changes['changed'][$uniq])
                                ? $changes['changed'][$uniq]
                                : $changes['added'][$uniq];

                            if (
                                isset($data['defaultValue'])
                                && $data['defaultValue']
                                && !$av->getDefaultValue()
                            ) {
                                $pr = $repo->findOneBy(
                                    array(
                                        'product'      => $product,
                                        'attribute'    => $this,
                                        'defaultValue' => true
                                    )
                                );
                                if ($pr) {
                                    $pr->setDefaultValue(false);
                                }
                            }

                            $repo->update($av, $data);

                            if (isset($changes['added'][$uniq])) {
                                unset($changes['added'][$uniq]);
                            }
                        }
                    }

                    if ($changes['added']) {
                        foreach ($changes['added'] as $uniq => $data) {
                            if (
                                isset($data['defaultValue'])
                                && $data['defaultValue']
                            ) {
                                $pr = $repo->findOneBy(
                                    array(
                                        'product'      => $product,
                                        'attribute'    => $this,
                                        'defaultValue' => true
                                    )
                                );
                                if ($pr) {
                                    $pr->setDefaultValue(false);
                                }
                            }
                            $av = $this->createAttributeValue($product);
                            if (self::TYPE_CHECKBOX == $this->getType()) {
                                $av->setValue($uniq);

                            } else {
                                $av->setAttributeOption(
                                    \XLite\Core\Database::getRepo('XLite\Model\AttributeOption')->find($uniq)
                                );
                            }
                            $repo->update($av, $data);
                        }
                    }
                    break;

                default:
            }
            \XLite\Core\Database::getEM()->flush();
        }
    }

    /**
     * Set attribute value
     *
     * @param \XLite\Model\Product $product Product
     * @param mixed                $data    Value
     *
     * @return void
     */
    public function setAttributeValue(\XLite\Model\Product $product, $data)
    {
        $class = $this->getAttributeValueClass($this->getType());
        $repo = \XLite\Core\Database::getRepo($class);

        if (self::TYPE_SELECT == $this->getType()) {
            $ids = array();
            foreach ($data['value'] as $id => $value) {
                $value = trim($value);
                if ($value && is_int($id)) {
                    if (!isset($data['deleteValue'][$id])) {
                        $attributeValue = $attributeOption = null;

                        if ($this->getProduct() && 0 < $id && !isset($data['ignoreIds'])) {
                            $attributeValue = $repo->find($id);
                            if ($attributeValue) {
                                $attributeOption = $attributeValue->getAttributeOption();
                                $attributeOption->setName($value);
                            }
                        }

                        if (!$attributeOption) {
                            $attributeOption = \XLite\Core\Database::getRepo('XLite\Model\AttributeOption')
                                ->findOneByNameAndAttribute($value, $this);
                        }

                        if (!$attributeOption) {
                            $attributeOption = new \XLite\Model\AttributeOption();
                            $attributeOption->setAttribute($this);
                            $attributeOption->setName($value);
                            \XLite\Core\Database::getEM()->persist($attributeOption);

                        } else {
                            $attributeValue = $repo->findOneBy(
                                array(
                                    'attribute_option' => $attributeOption,
                                    'product' => $product,
                                )
                            );
                        }

                        if (!$attributeValue && 0 < $id && !isset($data['ignoreIds'])) {
                            $attributeValue = $repo->find($id);
                        }

                        if ($attributeValue) {
                            $ids[$attributeValue->getId()] = $attributeValue->getId();

                        } else {
                            $attributeValue = $this->createAttributeValue($product);
                        }

                        $attributeValue->setAttributeOption($attributeOption);
                        $attributeValue->setDefaultValue(isset($data['default'][$id]));
                        foreach ($attributeValue::getModifiers() as $modifier => $options) {
                            if (isset($data[$modifier]) && isset($data[$modifier][$id])) {
                                $attributeValue->setModifier($data[$modifier][$id], $modifier);
                            }
                        }
                    }

                    if (!isset($data['multiple'])) {
                        break;
                    }
                }
            }

            foreach ($repo->findBy(array('product' => $product, 'attribute' => $this)) as $data) {
                if ($data->getId() && !isset($ids[$data->getId()])) {
                    $repo->delete($data);
                }
            }

        } elseif (self::TYPE_CHECKBOX == $this->getType() && isset($data['multiple'])) {
            foreach (array(true, false) as $value) {
                $attributeValue = $repo->findOneBy(
                    array(
                        'product'   => $product,
                        'attribute' => $this,
                        'value'     => $value,
                    )
                );

                if (!$attributeValue) {
                    $attributeValue = $this->createAttributeValue($product);
                    $attributeValue->setValue($value);
                }

                $value = intval($value);
                $attributeValue->setDefaultValue(isset($data['default'][$value]));
                foreach ($attributeValue::getModifiers() as $modifier => $options) {
                    if (isset($data[$modifier]) && isset($data[$modifier][$value])) {
                        $attributeValue->setModifier($data[$modifier][$value], $modifier);
                    }
                }
            }

        } else {
            $editable = is_array($data) ? !empty($data['editable']) : null;
            $value = is_array($data) ? $data['value'] : $data;
            if (is_array($value)) {
                $value = array_shift($value);
            }
            $delete = true;
            $attributeValue = null;

            if ('' !== $value || isset($editable)) {
                $attributeValue = $repo->findOneBy(array('product' => $product, 'attribute' => $this));

                if (!$attributeValue) {
                    $attributeValue = $this->createAttributeValue($product);
                    $delete = false;
                }

                $attributeValue->setValue($value);
                if (isset($editable)) {
                    $attributeValue->setEditable($editable);
                }
            }

            if ($delete) {
                foreach ($repo->findBy(array('product' => $product, 'attribute' => $this)) as $data) {
                    if (!$attributeValue || $attributeValue->getId() != $data->getId()) {
                        $repo->delete($data);
                    }
                }
            }
        }
    }

    /**
     * Get attribute value
     *
     * @param \XLite\Model\Product $product  Product
     * @param boolean              $asString As string flag OPTIONAL
     *
     * @return mixed
     */
    public function getAttributeValue(\XLite\Model\Product $product, $asString = false)
    {
        $repo = \XLite\Core\Database::getRepo($this->getAttributeValueClass($this->getType()));

        if (self::TYPE_SELECT == $this->getType() || self::TYPE_CHECKBOX == $this->getType()) {
            $attributeValue = $repo->findBy(array('product' => $product, 'attribute' => $this));

            if (
                $attributeValue
                && $asString
            ) {
                if (is_array($attributeValue)) {
                    foreach ($attributeValue as $k => $v) {
                        $attributeValue[$k] = $v->asString();
                    }

                } elseif (is_object($attributeValue)) {
                    $attributeValue = $attributeValue->asString();

                } elseif (self::TYPE_CHECKBOX == $this->getType()) {
                    $attributeValue = static::t('Yes');
                }
            }

        } else {
            $attributeValue = $repo->findOneBy(array('product' => $product, 'attribute' => $this));
            if ($attributeValue && $asString) {
                $attributeValue = $attributeValue->getValue();
            }
        }

        return $attributeValue;
    }

    /**
     * Get attribute value
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return mixed
     */
    public function getDefaultAttributeValue(\XLite\Model\Product $product)
    {
        $repo = \XLite\Core\Database::getRepo($this->getAttributeValueClass($this->getType()));

        $attributeValue = $repo->findOneBy(array('product' => $product, 'attribute' => $this, 'defaultValue' => true));
        if (!$attributeValue) {
            $attributeValue = $repo->findOneBy(array('product' => $product, 'attribute' => $this));
        }

        return $attributeValue;
    }

    /**
     * This attribute is multiple or not flag
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return boolean
     */
    public function isMultiple(\XLite\Model\Product $product)
    {
        $repo = \XLite\Core\Database::getRepo($this->getAttributeValueClass($this->getType()));

        return (!$this->getProduct() || $this->getProduct()->getId() == $product->getId())
            && (
                !$this->getProductClass()
                || ($product->getProductClass() && $this->getProductClass()->getId() == $product->getProductClass()->getId())
            )
            && 1 < count($repo->findBy(array('product' => $product, 'attribute' => $this)));
    }

    /**
     * Create attribute value
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return mixed
     */
    protected function createAttributeValue(\XLite\Model\Product $product)
    {
        $class = $this->getAttributeValueClass($this->getType());

        $attributeValue = new $class();
        $attributeValue->setProduct($product);
        $attributeValue->setAttribute($this);
        \XLite\Core\Database::getEM()->persist($attributeValue);

        return $attributeValue;
    }
}
