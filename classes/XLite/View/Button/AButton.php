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

namespace XLite\View\Button;

/**
 * Abstract button
 */
abstract class AButton extends \XLite\View\AView
{
    /**
     * Widget parameter names
     */
    const PARAM_NAME     = 'buttonName';
    const PARAM_VALUE    = 'value';
    const PARAM_LABEL    = 'label';
    const PARAM_STYLE    = 'style';
    const PARAM_DISABLED = 'disabled';
    const PARAM_ID       = 'id';
    const PARAM_ATTRIBUTES = 'attributes';
    const PARAM_BTN_SIZE   = 'button-size';
    const PARAM_BTN_TYPE   = 'button-type';
    const PARAM_ICON_STYLE = 'icon-style';

    const BTN_SIZE_DEFAULT = 'btn-default';

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'button/css/button.css';

        return $list;
    }

    /**
     * Get a list of JavaScript files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = 'button/js/button.js';

        return $list;
    }

    /**
     * Define the divider button (in cases of buttons list)
     *
     * @return boolean
     */
    public function isDivider()
    {
        return false;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'button/regular.tpl';
    }

    /**
     * getDefaultLabel
     *
     * @return string
     */
    protected function getDefaultLabel()
    {
        return '--- Button title is not defined ---';
    }

    /**
     * getDefaultStyle
     *
     * @return string
     */
    protected function getDefaultStyle()
    {
        return '';
    }

    /**
     * getDefaultDisableState
     *
     * @return boolean
     */
    protected function getDefaultDisableState()
    {
        return false;
    }

    /**
     * Get default attributes
     *
     * @return array
     */
    protected function getDefaultAttributes()
    {
        return array();
    }

    /**
     * Get attributes
     *
     * @return array
     */
    protected function getAttributes()
    {
        $list = $this->getParam(static::PARAM_ATTRIBUTES);

        return array_merge($list, $this->getButtonAttributes());
    }

    /**
     * Defines the button specific attributes
     *
     * @return array
     */
    protected function getButtonAttributes()
    {
        $list = array(
            'type' => 'button',
        );

        $class = $this->getClass();
        if ($class) {
            $list['class'] = $class;
        }

        if ($this->getId()) {
            $list['id'] = $this->getId();
        }

        if ($this->isDisabled()) {
            $list['disabled'] = 'disabled';
        }

        return $list;
    }

    /**
     * Return button text
     *
     * @return string
     */
    protected function getButtonLabel()
    {
        return $this->getParam(static::PARAM_LABEL);
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_NAME     => new \XLite\Model\WidgetParam\String('Name', '', true),
            static::PARAM_VALUE    => new \XLite\Model\WidgetParam\String('Value', '', true),
            static::PARAM_LABEL    => new \XLite\Model\WidgetParam\String('Label', $this->getDefaultLabel(), true),
            static::PARAM_STYLE    => new \XLite\Model\WidgetParam\String('Button style', $this->getDefaultStyle()),
            static::PARAM_BTN_SIZE => new \XLite\Model\WidgetParam\String('Button size', $this->getDefaultButtonSize()),
            static::PARAM_BTN_TYPE => new \XLite\Model\WidgetParam\String('Button type', $this->getDefaultButtonType()),
            static::PARAM_DISABLED => new \XLite\Model\WidgetParam\Bool('Disabled', $this->getDefaultDisableState()),
            static::PARAM_ID       => new \XLite\Model\WidgetParam\String('Button ID', ''),
            static::PARAM_ATTRIBUTES => new \XLite\Model\WidgetParam\Collection('Attributes', $this->getDefaultAttributes()),
            static::PARAM_ICON_STYLE => new \XLite\Model\WidgetParam\String('Button ID', ''),
        );
    }

    /**
     * Define the size of the button.
     *
     * @return string
     */
    protected function getDefaultButtonSize()
    {
        return '';
    }

    /**
     * Define the button type (btn-warning and so on)
     *
     * @return string
     */
    protected function getDefaultButtonType()
    {
        return 'regular-button';
    }

    /**
     * Get class
     *
     * @return string
     */
    protected function getClass()
    {
        return 'btn '
            . $this->getParam(static::PARAM_BTN_SIZE) . ' '
            . $this->getParam(static::PARAM_BTN_TYPE) . ' '
            . $this->getParam(static::PARAM_STYLE)
            . ($this->isDisabled() ? ' disabled' : '');
    }

    /**
     * getId
     *
     * @return string
     */
    protected function getId()
    {
        return $this->getParam(static::PARAM_ID);
    }

    /**
     * Return button name
     *
     * @return string
     */
    protected function getName()
    {
        return $this->getParam(static::PARAM_NAME);
    }

    /**
     * Return button value
     *
     * @return string
     */
    protected function getValue()
    {
        return $this->getParam(static::PARAM_VALUE);
    }

    /**
     * hasName
     *
     * @return void
     */
    protected function hasName()
    {
        return '' !== $this->getParam(static::PARAM_NAME);
    }

    /**
     * hasValue
     *
     * @return void
     */
    protected function hasValue()
    {
        return '' !== $this->getParam(static::PARAM_VALUE);
    }

    /**
     * hasClass
     *
     * @return string
     */
    protected function hasClass()
    {
        return '' !== $this->getParam(static::PARAM_STYLE);
    }

    /**
     * isDisabled
     *
     * @return boolean
     */
    protected function isDisabled()
    {
        return $this->getParam(static::PARAM_DISABLED);
    }
}
