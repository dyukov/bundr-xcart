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

namespace XLite\Module\XC\ProductVariants\Logic\OneCImport;

/**
 * Offers import
 *
 * @LC_Dependencies ("XC\OneCIntegration")
 */
abstract class Offers extends \XLite\Module\XC\OneCIntegration\Logic\OneCImport\Offers implements \XLite\Base\IDecorator
{
    /**
     * Assemble offer data
     *
     * @param \DOMNode $node Node
     *
     * @return array
     */
    protected function assembleOfferData(\DOMNode $node)
    {
        $data = parent::assembleOfferData($node);

        if ($data) {
            $xpath = new \DOMXPath($node->ownerDocument);

            // Prices
            $data['properties'] = array();
            foreach ($xpath->query('ХарактеристикиТовара/ХарактеристикаТовара', $node) as $node2) {
                $data['properties'][] = $this->convertDOMNodeToArray($node2);
            }
        }

        return $data;
    }

    /**
     * Get offer data rules
     *
     * @return array
     */
    protected function getOfferDataRules()
    {
        return parent::getOfferDataRules()
            + array(
                'skuS' => 'Штрихкод',
            );
    }

    /**
     * Update offer by offer data
     *
     * @param \XLite\Model\Product $model Product
     * @param array                $data  Offer data
     *
     * @return void
     */
    protected function updateOfferByData(\XLite\Model\Product $model, array $data)
    {
        if (empty($data['subid'])) {
            parent::updateOfferByData($model, $data);

        } elseif (!empty($data['properties'])) {
            $attributes = $this->assembleOfferAttributes($model, $data['properties']);
            $variant = $this->assembleOfferVariant($model, $attributes);
            if ($variant) {
                $this->updateOfferVariant($variant, $data);
            }
        }
    }

    /**
     * Assemble offer attributes 
     * 
     * @param \XLite\Model\Product $model      Product
     * @param array                $attributes Raw attributes pairs
     *  
     * @return array
     */
    protected function assembleOfferAttributes(\XLite\Model\Product $model, array $attributes)
    {
        $result = array();
        foreach ($attributes as $data) {
            $name = $data['Наименование'];
            $value = $data['Значение'];

            $cnd = new \XLite\Core\CommonCell;
            $cnd->product = $model;
            $cnd->type = \XLite\Model\Attribute::TYPE_SELECT;
            $cnd->name = $name;
            $list = \XLite\Core\Database::getRepo('XLite\Model\Attribute')->search($cnd);
            $attribute = $list ? reset($list) : null;

            $optionFound = false;
            $valueFound = false;
            if ($attribute) {

                // Global attribute - detect option
                foreach ($attribute->getAttributeOptions() as $option) {
                    if ($option->getName() == $value) {
                        $optionFound = true;
                        $method = 'getAttributeValue' . $attribute->getType();
                        foreach ($model->$method() as $avalue) {
                            if ($option->getId() == $avalue->getAttributeOption()->getId()) {
                                $valueFound = true;
                                break;
                            }
                        }

                        break;
                    }
                }

            } else {

                // New local attribute
                $attribute = new \XLite\Model\Attribute;
                \XLite\Core\Database::getEM()->persist($attribute);
                $attribute->setName($name);
                $attribute->setType(\XLite\Model\Attribute::TYPE_SELECT);
                $attribute->setProduct($model);
                $model->addAttributes($attribute);
            }

            if (!$optionFound) {

                // Create new option
                $option = new \XLite\Model\AttributeOption;
                \XLite\Core\Database::getEM()->persist($option);
                $option->setAttribute($attribute);
                $attribute->addAttributeOptions($option);
                $option->setName($value);
            }

            if (!$valueFound) {

                // Create new link option <-> product
                $avalue = $this->createAttributeLink($attribute, $model, $option);
            }

            $result[] = $avalue;
        }

        return $result;
    }

    /**
     * Assemble offer variant 
     * 
     * @param \XLite\Model\Product $model      Product
     * @param array                $attributes Attributes list
     *  
     * @return \XLite\Module\XC\ProductVariants\Model\ProductVariant
     */
    protected function assembleOfferVariant(\XLite\Model\Product $model, array $attributes)
    {
        $variant = $model->getVariantByAttributeValues($attributes);

        if (!$variant) {
            $variant = new \XLite\Module\XC\ProductVariants\Model\ProductVariant();
            foreach ($attributes as $attributeValue) {
                $attribute = $attributeValue->getAttribute();
                $method = 'addAttributeValue' . $attribute->getType();
                $variant->$method($attributeValue);
                $attributeValue->addVariants($variant);

                $attributeFound = false;
                foreach ($model->getVariantsAttributes() as $a) {
                    if ($a->getId() == $attribute->getId()) {
                        $attributeFound = true;
                    }
                }

                if (!$attributeFound) {
                    $model->addVariantsAttributes($attribute);
                    $attribute->addVariantsProduct($model);
                }                
            }

            $variant->setProduct($model);
            $model->addVariants($variant);
            \XLite\Core\Database::getEM()->persist($variant);
        }

        return $variant;
    }

    /**
     * Update offer variant 
     * 
     * @param \XLite\Module\XC\ProductVariants\Model\ProductVariant $variant Variant
     * @param array                                                 $data    Variant data
     *  
     * @return void
     */
    protected function updateOfferVariant(\XLite\Module\XC\ProductVariants\Model\ProductVariant $variant, array $data)
    {
        $variant->setId1c($data['id1c'] . '#' . $data['subid']);

        $price = doubleval($data['price']['ЦенаЗаЕдиницу']);
        if ($variant->getProduct()->getPrice() != $price) {
            $variant->setPrice($price);
            $variant->setDefaultPrice(false);

        } else {
            $variant->setDefaultPrice(true);
        }

        if (!empty($data['skuS'])) {
            $variant->setSKU($data['skuS']);
        }

        if (isset($data['amount'])) {
            $variant->setAmount(intval($data['amount']));
            $variant->setDefaultAmount(false);

        } else {
            $variant->setDefaultAmount(true);
        }
    }
}
