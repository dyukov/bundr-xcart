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

namespace XLite\View\FormField\Select\Model;

/**
 * Form abstract selector
 */
abstract class AModel extends \XLite\View\FormField\AFormField
{
    /**
     * Widget param names
     */
    const PARAM_MIN_COUNT               = 'min_count';
    const PARAM_EMPTY_PHRASE            = 'empty_phrase';
    const PARAM_EMPTY_MODEL_DEFINITION  = 'empty_model_definition';
    const PARAM_GETTER                  = 'getter';
    const PARAM_PLACEHOLDER             = 'placeholder';
    const PARAM_IS_MODEL_REQUIRED       = 'is_model_required';

    /**
     * Defines the text value of the model
     *
     * @return string
     */
    abstract protected function getTextValue();

    /**
     * Return field type
     *
     * @return string
     */
    public function getFieldType()
    {
        return static::FIELD_TYPE_TEXT;
    }

    /**
     * Defines the JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'form_field/model_selector/controller.js';

        return $list;
    }

    /**
     * Defines the CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'form_field/model_selector/style.css';

        return $list;
    }

    /**
     * Return field template
     *
     * @return string
     */
    protected function getFieldTemplate()
    {
        return 'model_selector.tpl';
    }

    /**
     * Defines the name of the text value input
     *
     * @return string
     */
    protected function getTextName()
    {
        return '';
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
            static::PARAM_MIN_COUNT => new \XLite\Model\WidgetParam\Int(
                'Minimum symbols count after which the search will be permitted', $this->getDefaultMinCount(), false
            ),
            static::PARAM_EMPTY_PHRASE => new \XLite\Model\WidgetParam\String(
                'Text for the empty list', $this->getDefaultEmptyPhrase(), false
            ),
            static::PARAM_EMPTY_MODEL_DEFINITION => new \XLite\Model\WidgetParam\String(
                'Text when no model is selected', $this->getDefaultEmptyModelDefinition(), false
            ),
            static::PARAM_GETTER => new \XLite\Model\WidgetParam\String(
                'URL which will be the getter of the search objects', $this->getDefaultGetter(), false
            ),
            static::PARAM_PLACEHOLDER => new \XLite\Model\WidgetParam\String(
                'Text for the placeholder', '', false
            ),
            static::PARAM_IS_MODEL_REQUIRED => new \XLite\Model\WidgetParam\Bool(
                'Flag if the model required to be selected', true, false
            ),
        );
    }

    /**
     * Defines the minimum symbols which must be entered to request the model
     *
     * @return integer
     */
    protected function getDefaultMinCount()
    {
        return 3;
    }

    /**
     * Defines the text phrase if no models are found
     *
     * @return string
     */
    protected function getDefaultEmptyPhrase()
    {
        return static::t('No items found');
    }

    /**
     * Defines the text if no model is selected
     *
     * @return string
     */
    protected function getDefaultEmptyModelDefinition()
    {
        return static::t('No model selected');
    }

    /**
     * Defines the URL to request the models
     *
     * @return string
     */
    protected function getDefaultGetter()
    {
        return '';
    }

    /**
     * Defines the model specific JS-namespace event
     *
     * @return string
     */
    protected function getDataType()
    {
        return 'model';
    }

    /**
     * This data will be accessible using JS core.getCommentedData() method.
     *
     * @return array
     */
    protected function getCommentedData()
    {
        return array(
            static::PARAM_MIN_COUNT     => $this->getParam(static::PARAM_MIN_COUNT),
            static::PARAM_GETTER        => $this->getParam(static::PARAM_GETTER),
        );
    }

    /**
     * Register the CSS classes to be set to the widget
     *
     * @return array
     */
    protected function defineCSSClasses()
    {
        $result = array(
            'model-input-selector',
            'field-model-selector',
            'form-control',
        );

        if ($this->getParam(static::PARAM_IS_MODEL_REQUIRED)) {
            $result[] = 'model-required';
        }

        return $result;
    }

    /**
     * Defines the CSS class value
     *
     * @see self::defineCSSClasses()
     *
     * @return string
     */
    protected function getCSSClasses()
    {
        return implode(' ', $this->defineCSSClasses());
    }
}
