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
 * Front page view model
 */
class FrontPage extends \XLite\View\Model\Category
{
    /**
     * We add 'Root category listings format' widget into the default section
     *
     * @param array $params
     * @param array $sections
     */
    public function __construct(array $params = array(), array $sections = array())
    {
        parent::__construct($params, $sections);

        $schema = array();

        foreach ($this->schemaDefault as $name => $value) {
            $schema[$name] = $value;

            if ('description' == $name) {
                $schema['root_category_look'] = array(
                    self::SCHEMA_CLASS    => 'XLite\View\FormField\Select\RootCategoriesLook',
                    self::SCHEMA_LABEL    => 'Root category listings format',
                    self::SCHEMA_REQUIRED => false,
                );
            }
        }

        $this->schemaDefault = $schema;
    }

    /**
     * Get default value for the field
     *
     * @param string $fieldName Field service name
     *
     * @return mixed
     */
    public function getDefaultFieldValue($fieldName)
    {
        $value = parent::getDefaultFieldValue($fieldName);

        if (
            'root_category_look' == $fieldName
            && !$value
        ) {
            $value = \XLite\View\Subcategories::DEFAULT_DISPLAY_MODE;
        }

        return $value;
    }

    /**
     * Return list of form fields objects by schema
     *
     * @param array $schema Field descriptions
     *
     * @return array
     */
    protected function getFieldsBySchema(array $schema)
    {
        $newSchema = array();
        foreach ($this->getIncludedFields() as $name) {
            if (!empty($schema[$name])) {
                $newSchema[$name] = $schema[$name];
            }
        }

        return parent::getFieldsBySchema($newSchema);
    }

    /**
     * Get included fields 
     * 
     * @return array
     */
    protected function getIncludedFields()
    {
        return array('show_title', 'name', 'description', 'root_category_look', 'meta_title', 'meta_tags', 'meta_desc');
    }

    /**
     * Return current model ID
     *
     * @return integer
     */
    public function getModelId()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Category')->getRootCategoryId();
    }

    /**
     * This object will be used if another one is not pased
     *
     * @return \XLite\Model\Category
     */
    protected function getDefaultModelObject()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Category')->getRootCategory();
    }

    /**
     * Return name of web form widget class
     *
     * @return string
     */
    protected function getFormClass()
    {
        return '\XLite\View\Form\Model\FrontPage';
    }

    /**
     * Add top message
     *
     * @return void
     */
    protected function addDataSavedTopMessage()
    {
        \XLite\Core\TopMessage::addInfo('The front page has been updated');
    }

}
