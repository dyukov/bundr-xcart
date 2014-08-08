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

namespace XLite\Logic\Export\Step;

/**
 * Products
 */
class Products extends \XLite\Logic\Export\Step\Base\I18n
{
    // {{{ Data

    /**
     * Get repository
     *
     * @return \XLite\Model\Repo\ARepo
     */
    protected function getRepository()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Product');
    }

    /**
     * Get filename
     *
     * @return string
     */
    protected function getFilename()
    {
        return 'products.csv';
    }

    // }}}

    // {{{ Columns

    /**
     * Define columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = array(
            'sku'                       => array(),
            'price'                     => array(),
            'memberships'               => array(),
            'productClass'              => array(),
            'taxClass'                  => array(),
            'enabled'                   => array(),
            'weight'                    => array(),
            'freeShipping'              => array(),
            'images'                    => array(),
            'imagesAlt'                 => array(),
            'arrivalDate'               => array(),
            'date'                      => array(),
            'updateDate'                => array(),
            'categories'                => array(),
            'inventoryTrackingEnabled'  => array(),
            'stockLevel'                => array(),
            'lowLimitEnabled'           => array(),
            'lowLimitLevel'             => array(),
            'cleanURL'                  => array(),
            'useSeparateBox'            => array(),
            'boxWidth'                  => array(),
            'boxLength'                 => array(),
            'boxHeight'                 => array(),
            'itemsPerBox'               => array(),
        );

        $columns += $this->assignI18nColumns(
            array(
                'name'             => array(),
                'description'      => array(),
                'briefDescription' => array(),
                'metaTags'         => array(),
                'metaDesc'         => array(),
                'metaTitle'        => array(),
            )
        );

        $columns += $this->getAttributesColumns();

        return $columns;
    }

    /**
     * Get product attributes columns
     *
     * @return array
     */
    protected function getAttributesColumns()
    {
        $columns = array();

        foreach (\XLite\Core\Database::getRepo('XLite\Model\Attribute')->findAll() as $attribute) {

            $skip = false;

            if ('global' == $this->generator->getOptions()->attrs) {
                // Only global attributes are allowed
                $skip = $attribute->getProduct() || $attribute->getProductClass();

            } elseif ('global' == $this->generator->getOptions()->attrs) {
                // Skip custom product attributes
                $skip = $attribute->getProduct();
            }

            if ($skip) {
                continue;
            }

            $columns[$this->getUniqueFieldName($attribute)] = $this->getAttributeColumn($attribute);
        }

        return $columns;
    }

    /**
     * Get attribute column data
     *
     * @param \XLite\Model\Attribute $attribute Attribute object
     *
     * @return array
     */
    protected function getAttributeColumn($attribute)
    {
        return array(
            static::COLUMN_GETTER => 'getAttributeColumnValue',
            'attribute'           => $attribute,
        );
    }

    /**
     * Return unique field name
     *
     * @param \XLite\Model\Attribute $attribute Attribute
     *
     * @return string
     */
    protected function getUniqueFieldName(\XLite\Model\Attribute $attribute)
    {
        $result = $attribute->getName() . ' (field:';

        $cnd = new \XLite\Core\CommonCell;
        $cnd->name = $attribute->getName();

        if ($attribute->getProduct()) {
            $result .= 'product';

        } elseif ($attribute->getProductClass()) {
            $result .= 'class';

        } else {
            $result .= 'global';
        }

        if ($attribute->getAttributeGroup()) {
            $result .= ' >>> ' . $attribute->getAttributeGroup()->getName();
        }

        $result .= ')';

        return $result;
    }

    // }}}

    // {{{ Getters and formatters

    /**
     * Get column value for 'sku' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getSkuColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'sku');
    }

    /**
     * Get column value for 'price' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getPriceColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'price');
    }

    /**
     * Get column value for 'productClass' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getProductClassColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->getProductClass();
    }

    /**
     * Get column value for 'memberships' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getMembershipsColumnValue(array $dataset, $name, $i)
    {
        $result = array();

        foreach ($dataset['model']->getMemberships() as $membership) {
            $result[] = $membership->getName();
        }

        return $result;
    }

    /**
     * Get column value for 'taxClass' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getTaxClassColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->getTaxClass();
    }

    /**
     * Get column value for 'enabled' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getEnabledColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'enabled');
    }

    /**
     * Get column value for 'weight' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getWeightColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'weight');
    }

    /**
     * Get column value for 'freeShipping' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getFreeShippingColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'free_shipping');
    }

    /**
     * Get column value for 'images' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return array
     */
    protected function getImagesColumnValue(array $dataset, $name, $i)
    {
        $result = array();

        foreach ($dataset['model']->getImages() as $image) {
            $result[] = $this->formatImageModel($image);
        }

        return $result;
    }

    /**
     * Get column value for 'imagesAlt' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return array
     */
    protected function getImagesAltColumnValue(array $dataset, $name, $i)
    {
        $result = array();

        foreach ($dataset['model']->getImages() as $image) {
            $result[] = $image->getAlt();
        }

        return $result;
    }

    /**
     * Get column value for 'arrivalDate' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getArrivalDateColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'arrivalDate');
    }

    /**
     * Format 'arrivalDate' field value
     *
     * @param mixed  $value   Value
     * @param array  $dataset Dataset
     * @param string $name    Column name
     *
     * @return string
     */
    protected function formatArrivalDateColumnValue($value, array $dataset, $name)
    {
        return $this->formatTimestamp($value);
    }

    /**
     * Get column value for 'date' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getDateColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'date');
    }

    /**
     * Format 'date' field value
     *
     * @param mixed  $value   Value
     * @param array  $dataset Dataset
     * @param string $name    Column name
     *
     * @return string
     */
    protected function formatDateColumnValue($value, array $dataset, $name)
    {
        return $this->formatTimestamp($value);
    }

    /**
     * Get column value for 'updateDate' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getUpdateDateColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'updateDate');
    }

    /**
     * Format 'updateDate' field value
     *
     * @param mixed  $value   Value
     * @param array  $dataset Dataset
     * @param string $name    Column name
     *
     * @return string
     */
    protected function formatUpdateDateColumnValue($value, array $dataset, $name)
    {
        return $this->formatTimestamp($value);
    }

    /**
     * Get column value for 'categories' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return array
     */
    protected function getCategoriesColumnValue(array $dataset, $name, $i)
    {
        $result = array();
        foreach ($dataset['model']->getCategories() as $category) {
            $path = array();
            foreach ($category->getRepository()->getCategoryPath($category->getCategoryId()) as $c) {
                $path[] = $c->getName();
            }
            $result[] = implode(' >>> ', $path);
        }

        return $result;
    }

    /**
     * Get column value for 'inventoryTrackingEnabled' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getInventoryTrackingEnabledColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->getInventory()->getEnabled();
    }

    /**
     * Get column value for 'stockLevel' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getStockLevelColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->getInventory()->getAmount();
    }

    /**
     * Get column value for 'lowLimitEnabled' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getLowLimitEnabledColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->getInventory()->getLowLimitEnabled();
    }

    /**
     * Get column value for 'lowLimitLevel' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getLowLimitLevelColumnValue(array $dataset, $name, $i)
    {
        $inventory = $dataset['model']->getInventory();

        return $inventory->getLowLimitAmount();
    }

    /**
     * Get column value for 'cleanUrl' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getCleanUrlColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'cleanUrl');
    }

    /**
     * Get column value for 'useSeparateBox' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getUseSeparateBoxColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'useSeparateBox');
    }

    /**
     * Get column value for 'boxWidth' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getBoxWidthColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'boxWidth');
    }

    /**
     * Get column value for 'boxLength' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getBoxLengthColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'boxLength');
    }

    /**
     * Get column value for 'itemsPerBox' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getItemsPerBoxColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'itemsPerBox');
    }

    /**
     * Get column value for 'boxHeight' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getBoxHeightColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'boxHeight');
    }

    /**
     * Get column value for abstract 'attribute' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getAttributeColumnValue(array $dataset, $name, $i)
    {
        $columns = $this->getColumns();
        $column = $columns[$name];
        $result = array();

        $repo = \XLite\Core\Database::getRepo(
            $column['attribute']->getAttributeValueClass(
                $column['attribute']->getType()
            )
        );

        $values = $repo->findBy(array('product' => $dataset['model'], 'attribute' => $column['attribute']));

        if ($values) {
            $isMultiple = $column['attribute']->isMultiple($dataset['model']);
            foreach ($values as $value) {
                $modifiers = array();
                if ($isMultiple) {
                    if ($value->getDefaultValue()) {
                        $modifiers[] = 'default';
                    }
                    foreach ($value->getModifiers() as $field => $modifier) {
                        $str = $value->getModifier($field);
                        if ($str) {
                            $modifiers[] .= $modifier['symbol'] . $str;
                        }
                    }
                }

                $result[] = $value->asString() . ($modifiers ? '=' . implode('/', $modifiers)  : '');
            }
        }

        return $result;
    }

    /**
     * Copy resource
     *
     * @param \XLite\Model\Base\Storage $storage      Storage
     * @param string                    $subdirectory Subdirectory
     *
     * @return boolean
     */
    protected function copyResource(\XLite\Model\Base\Storage $storage, $subdirectory)
    {
        if ($storage instanceOf \XLite\Model\Base\Image) {
            $subdirectory .= LC_DS . 'products';
        }

        return parent::copyResource($storage, $subdirectory);
    }

    // }}}

}
