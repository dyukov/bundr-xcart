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
 * Attribute view model
 */
class Attribute extends \XLite\View\Model\AModel
{
    /**
     * Shema default
     *
     * @var array
     */
    protected $schemaDefault = array(
        'name' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Attribute',
            self::SCHEMA_REQUIRED => true,
        ),
        'attribute_group' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Select\AttributeGroups',
            self::SCHEMA_LABEL    => 'Attribute group',
            self::SCHEMA_REQUIRED => false,
        ),
        'type' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Select\AttributeTypes',
            self::SCHEMA_LABEL    => 'Type',
            self::SCHEMA_REQUIRED => false,
        ),
    );

    /**
     * Return current model ID
     *
     * @return integer
     */
    public function getModelId()
    {
        return \XLite\Core\Request::getInstance()->id;
    }

    /**
     * Defines the CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'attribute/style.css';

        return $list;

    }

    /**
     * Return fields list by the corresponding schema
     *
     * @return array
     */
    protected function getFormFieldsForSectionDefault()
    {
        if ($this->getModelObject()->getId()) {
            $this->schemaDefault['type'][self::SCHEMA_COMMENT] = 'Before editing attributes specific for the chosen type you should save the changes';

            if (
                $this->getModelObject()->getAttributeValuesCount()
                || (
                    $this->getModelObject()->getProductClass()
                    && $this->getModelObject()->getProductClass()->getProductsCount()
                )
            ) {
                $this->schemaDefault['type'][self::SCHEMA_COMMENT] = 'Attribute data will be lost. warning text';
            }

            if (
                \XLite\Model\Attribute::TYPE_SELECT == $this->getModelObject()->getType()
            ) {
                $this->schemaDefault['values'] = array(
                    self::SCHEMA_CLASS    => 'XLite\View\FormField\ItemsList',
                    self::SCHEMA_LABEL    => 'Attribute values',
                    \XLite\View\FormField\ItemsList::PARAM_LIST_CLASS => 'XLite\View\ItemsList\Model\AttributeOption',
                    \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'custom-field type-' . $this->getModelObject()->getType(),
                );
            }

            if (
                \XLite\Model\Attribute::TYPE_CHECKBOX == $this->getModelObject()->getType()
            ) {
                $this->schemaDefault['addToNew'] = array(
                    self::SCHEMA_CLASS     => '\XLite\View\FormField\Select\CheckboxList\YesNo',
                    self::SCHEMA_LABEL     => 'Default value',
                    \XLite\View\FormField\AFormField::PARAM_HELP          => 'This value will be added to new products or class’s assigns automatically',
                    \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'custom-field type-' . $this->getModelObject()->getType(),
                );
            }
        }

        return $this->getFieldsBySchema($this->schemaDefault);
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
        $data['attribute_group'] = \XLite\Core\Database::getRepo('XLite\Model\AttributeGroup')
            ->find($data['attribute_group']);

        if (!isset($data['addToNew'])) {
            $data['addToNew'] = array();
        }

        parent::setModelProperties($data);

        $this->getModelObject()->setProductClass($this->getProductClass());
    }

    /**
     * This object will be used if another one is not pased
     *
     * @return \XLite\Model\Attribute
     */
    protected function getDefaultModelObject()
    {
        $model = $this->getModelId()
            ? \XLite\Core\Database::getRepo('XLite\Model\Attribute')->find($this->getModelId())
            : null;

        return $model ?: new \XLite\Model\Attribute;
    }

    /**
     * Return name of web form widget class
     *
     * @return string
     */
    protected function getFormClass()
    {
        return '\XLite\View\Form\Model\Attribute';
    }

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass()
            . (
                $this->getModelObject()->getId()
                ? ' attribute-type-' . $this->getModelObject()->getType()
                : ''
            );
    }

    /**
     * Return list of the "Button" widgets
     *
     * @return array
     */
    protected function getFormButtons()
    {
        $result = parent::getFormButtons();

        $label = $this->getModelObject()->getId() ? 'Save changes' : 'Next wizard';

        $result['submit'] = new \XLite\View\Button\Submit(
            array(
                \XLite\View\Button\AButton::PARAM_LABEL => $label,
                \XLite\View\Button\AButton::PARAM_STYLE => 'action ' . ($this->getModelObject()->getId() ? 'save' : 'next'),
            )
        );

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
            \XLite\Core\TopMessage::addInfo('The attribute has been updated');

        } else {
            \XLite\Core\TopMessage::addInfo('The attribute has been added');
        }
    }
}
