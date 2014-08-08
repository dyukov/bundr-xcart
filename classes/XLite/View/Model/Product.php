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

namespace XLite\View\Model;

/**
 * Product view model
 */
class Product extends \XLite\View\Model\AModel
{
    /**
     * Schema default
     *
     * @var array
     */
    protected $schemaDefault = array(
        'sku' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text\SKU',
            self::SCHEMA_LABEL    => 'SKU',
            self::SCHEMA_REQUIRED => false,
        ),
        'name' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Product Name',
            self::SCHEMA_REQUIRED => true,
        ),
        'categories' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Select\Categories',
            self::SCHEMA_LABEL    => 'Category',
            self::SCHEMA_REQUIRED => false,
        ),
        'images' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\FileUploader\Image',
            self::SCHEMA_LABEL    => 'Images',
            self::SCHEMA_REQUIRED => false,
            \XLite\View\FormField\FileUploader\Image::PARAM_MULTIPLE => true,
        ),
        'memberships' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Select\Memberships',
            self::SCHEMA_LABEL    => 'Memberships',
            self::SCHEMA_REQUIRED => false,
        ),
        'taxClass' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Select\TaxClass',
            self::SCHEMA_LABEL    => 'Tax class',
            self::SCHEMA_REQUIRED => false,
        ),
        'price' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Price',
            self::SCHEMA_REQUIRED => true,
        ),
        'weight' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Weight',
            self::SCHEMA_REQUIRED => false,
        ),
        'shippable' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Select\YesNo',
            self::SCHEMA_LABEL    => 'Shippable',
            self::SCHEMA_REQUIRED => false,
        ),
        'useSeparateBox' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Select\Product\UseSeparateBox',
            self::SCHEMA_LABEL    => 'Ship in a separate box',
            self::SCHEMA_REQUIRED => false,
        ),
        'enabled' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Select\YesNo',
            self::SCHEMA_LABEL    => 'Available for sale',
            self::SCHEMA_REQUIRED => false,
        ),
        'arrivalDate' => array(
            self::SCHEMA_CLASS    => 'XLite\View\DatePicker',
            self::SCHEMA_LABEL    => 'Arrival date',
            \XLite\View\FormField\AFormField::PARAM_FIELD_ONLY => false,
            self::SCHEMA_REQUIRED => false,
        ),
        'meta_title' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Product page title',
            self::SCHEMA_REQUIRED => false,
        ),
        'brief_description' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Textarea\Advanced',
            self::SCHEMA_LABEL    => 'Brief description',
            self::SCHEMA_REQUIRED => false,
        ),
        'description' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Textarea\Advanced',
            self::SCHEMA_LABEL    => 'Full description',
            self::SCHEMA_REQUIRED => false,
        ),
        'meta_tags' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Meta keywords',
            self::SCHEMA_REQUIRED => false,
        ),
        'meta_desc' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Meta description',
            self::SCHEMA_REQUIRED => false,
        ),
        'cleanURL' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text\CleanURL',
            self::SCHEMA_LABEL    => 'Clean URL',
            self::SCHEMA_REQUIRED => false,
            \XLite\View\FormField\Input\Text\CleanURL::PARAM_OBJECT_CLASS_NAME  => '\XLite\Model\Product',
            \XLite\View\FormField\Input\Text\CleanURL::PARAM_OBJECT_ID_NAME     => 'product_id',
            \XLite\View\FormField\Input\Text\CleanURL::PARAM_ID                 => 'cleanurl',
        ),

    );

    /**
     * Return current model ID
     *
     * @return integer
     */
    public function getModelId()
    {
        return \XLite\Core\Request::getInstance()->product_id;
    }

    /**
     * getDefaultFieldValue
     *
     * @param string $name Field name
     *
     * @return mixed
     */
    public function getDefaultFieldValue($name)
    {
        $value = parent::getDefaultFieldValue($name);

        // Categories can be provided via request
        if ('categories' === $name) {
            $categoryId = \XLite\Core\Request::getInstance()->category_id;
            $value = $categoryId ? array(
                \XLite\Core\Database::getRepo('XLite\Model\Category')->find($categoryId),
            ) : $value;
        }

        return $value;
    }

    /**
     * This object will be used if another one is not pased
     *
     * @return \XLite\Model\Category
     */
    protected function getDefaultModelObject()
    {
        $model = $this->getModelId()
            ? \XLite\Core\Database::getRepo('XLite\Model\Product')->find($this->getModelId())
            : null;

        return $model ?: new \XLite\Model\Product;
    }

    /**
     * Defines the category products links collection
     *
     * @param \XLite\Model\Product $product
     * @param array                $categories
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function getCategoryProducts($product, $categories)
    {
        $links = array();
        foreach ($categories as $category) {
            $links[] = new \XLite\Model\CategoryProducts(
                array(
                    'category'    => $category,
                    'product'     => $product,
                    'orderby'     => $product->getOrderby($category->getCategoryId()),
                )
            );
        }

        return new \Doctrine\Common\Collections\ArrayCollection($links);
    }

    /**
     * Populate model object properties by the passed data
     *
     * @param array $data Data to set
     *
     * @return void
     */
    protected function setModelProperties(array $data)
    {
        if ($this->isValid()) {
            $this->updateModelProperties($data);
        }
    }

    /**
     * Populate model object properties by the passed data.
     * Specific wrapper for setModelProperties method.
     *
     * @param array $data Data to set
     *
     * @return void
     */
    protected function updateModelProperties(array $data)
    {
        $categories = isset($data['categories']) ? $data['categories'] : array();
        unset($data['categories']);

        $memberships = isset($data['memberships']) ? $data['memberships'] : array();
        unset($data['memberships']);

        // Flag variables
        foreach (array('enabled', 'shippable', 'useSeparateBox') as $value) {
            if (isset($data[$value]) && is_string($data[$value])) {
                $data[$value] = 'Y' == $data[$value];
            }
        }

        if (isset($data['useSeparateBox']) && $data['useSeparateBox']) {
            foreach (array('boxLength', 'boxWidth', 'boxHeight', 'itemsPerBox') as $var) {
                $data[$var] = $this->getPostedData($var);
            }
        }

        if ($this->getPostedData('autogenerateCleanURL')) {
            $data['cleanURL'] = \XLite\Core\Database::getRepo('XLite\Model\Product')->generateCleanURL(
                $this->getDefaultModelObject(),
                $data['name']
            );
        }

        $time = \XLite\Core\Converter::time();

        if (isset($data['arrivalDate'])) {
            $data['arrivalDate'] = intval(strtotime($data['arrivalDate'])) ? : mktime(0, 0, 0, date('m', $time), date('j', $time), date('Y', $time));
        }

        if (isset($data['productClass'])) {
            $data['productClass'] = \XLite\Core\Database::getRepo('XLite\Model\ProductClass')->find($data['productClass']);
        }

        if (isset($data['taxClass'])) {
            $data['taxClass'] = \XLite\Core\Database::getRepo('XLite\Model\TaxClass')->find($data['taxClass']);
        }

        parent::setModelProperties($data);

        $model = $this->getModelObject();

        $isNew = !$model->isPersistent();
        $model->update();

        if ($isNew) {
            \XLite\Core\Database::getRepo('XLite\Model\Attribute')->generateAttributeValues($model);

            $inventory = new \XLite\Model\Inventory();
            $inventory->setProduct($model);
            $model->setInventory($inventory);
        }

        $categories = \XLite\Core\Database::getRepo('XLite\Model\Category')->findByIds($categories);

        \XLite\Core\Database::getRepo('XLite\Model\CategoryProducts')->deleteInBatch(
            $model->getCategoryProducts()->toArray()
        );

        \XLite\Core\Database::getRepo('XLite\Model\Product')->update(
            $model,
            array('categoryProducts' => $this->getCategoryProducts($model, $categories))
        );

        // Update SKU
        if (is_null($model->getSku())) {
            $model->setSku(\XLite\Core\Database::getRepo('XLite\Model\Product')->generateSKU($model));
        }

        // Update memberships
        foreach ($model->getMemberships() as $membership) {
            $membership->getProducts()->removeElement($model);
        }

        $model->getMemberships()->clear();

        if (isset($memberships) && $memberships) {
            // Add new links
            foreach ($memberships as $mid) {
                $membership = \XLite\Core\Database::getRepo('XLite\Model\Membership')->find($mid);
                if ($membership) {
                    $model->addMemberships($membership);
                    $membership->addProduct($model);
                }
            }
        }

        // Set the controller model product
        $this->setProduct($model);
    }

    /**
     * Return name of web form widget class
     *
     * @return string
     */
    protected function getFormClass()
    {
        return 'XLite\View\Form\Model\Product';
    }

    /**
     * Return list of the "Button" widgets
     *
     * @return array
     */
    protected function getFormButtons()
    {
        $result = parent::getFormButtons();

        $result['submit'] = new \XLite\View\Button\Submit(
            array(
                \XLite\View\Button\AButton::PARAM_LABEL => $this->getModelObject()->getId() ? 'Update product' : 'Add product',
                \XLite\View\Button\AButton::PARAM_STYLE => 'action',
            )
        );

        if ($this->getModelObject()->isPersistent()) {
            $result['clone-product'] = new \XLite\View\Button\Link(
                array(
                    \XLite\View\Button\AButton::PARAM_LABEL => 'Clone this product',
                    \XLite\View\Button\AButton::PARAM_STYLE => 'model-button',
                    \XLite\View\Button\Link::PARAM_LOCATION => $this->buildURL(
                        'product',
                        'clone',
                        array(
                            'product_id' => $this->getModelObject()->getId(),
                        )
                    ),
                )
            );

            $result['preview-product'] = new \XLite\View\Button\SimpleLink(
                array(
                    \XLite\View\Button\AButton::PARAM_LABEL => 'Preview product page',
                    \XLite\View\Button\AButton::PARAM_STYLE => 'model-button link action',
                    \XLite\View\Button\Link::PARAM_BLANK    => true,
                    \XLite\View\Button\Link::PARAM_LOCATION => $this->buildProductPreviewURL($this->getModelObject()->getId()),
                )
            );
        }

        return $result;
    }

    /**
     * Add top message
     *
     * @return void
     */
    protected function addDataSavedTopMessage()
    {
        if ('create' != $this->currentAction) {
            \XLite\Core\TopMessage::addInfo('The product has been updated');

        } else {
            \XLite\Core\TopMessage::addInfo('The product has been added');
        }
    }

}
