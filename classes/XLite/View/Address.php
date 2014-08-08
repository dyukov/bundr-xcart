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

namespace XLite\View;

/**
 * \XLite\View\Address
 */
class Address extends \XLite\View\Dialog
{
    /**
     * Widget parameter names
     */
    const PARAM_DISPLAY_MODE    = 'displayMode';
    const PARAM_ADDRESS         = 'address';
    const PARAM_DISPLAY_WRAPPER = 'displayWrapper';

    /**
     * Allowed display modes
     */
    const DISPLAY_MODE_TEXT = 'text';
    const DISPLAY_MODE_FORM = 'form';

    /**
     * Service constants for schema definition
     */
    const SCHEMA_CLASS    = 'class';
    const SCHEMA_LABEL    = 'label';
    const SCHEMA_REQUIRED = 'required';

    /**
     * Schema
     *
     * @var array
     */
    protected $schema = array();

    /**
     * Get schema fields
     *
     * @return array
     */
    public function getSchemaFields()
    {
        $result = $this->schema;

        foreach (
            \XLite\Core\Database::getRepo('XLite\Model\AddressField')->search(new \XLite\Core\CommonCell(array('enabled' => true))) as $field
        ) {
            $result[$field->getServiceName()] = array(
                static::SCHEMA_CLASS    => $field->getSchemaClass(),
                static::SCHEMA_LABEL    => $field->getName(),
                static::SCHEMA_REQUIRED => $field->getRequired(),
            );
        }

        return $result;
    }

    /**
     * Get field style
     *
     * @param string $fieldName Field name
     *
     * @return string
     */
    protected function getFieldStyle($fieldName)
    {
        $result = 'address-text-cell address-text-' . $fieldName;

        if (\XLite\Core\Database::getRepo('XLite\Model\AddressField')->findOneBy(array('serviceName' => $fieldName, 'additional' => true))) {
            $result .= ' additional-field';
        }

        return $result;
    }

    /**
     * Get field value
     *
     * @param string  $fieldName    Field name
     * @param boolean $processValue Process value flag OPTIONAL
     *
     * @return string
     */
    public function getFieldValue($fieldName, $processValue = false)
    {
        $address = $this->getParam(self::PARAM_ADDRESS);

        $methodName = 'get' . \XLite\Core\Converter::getInstance()->convertToCamelCase($fieldName);

        // $methodName assembled from 'get' + camelized $fieldName
        $result = $address->$methodName();

        if ($result && false !== $processValue) {
            switch ($fieldName) {
                case 'state_id':
                    $result = $address->getCountry()->hasStates()
                        ? $address->getState()->getState()
                        : null;
                    break;

                case 'custom_state':
                    $result = $address->getCountry()->hasStates()
                        ? null
                        : $result;
                    break;

                case 'country_code':
                    $result = $address->getCountry()->getCountry();
                    break;

                default:
            }
        }

        return $result;
    }

    /**
     * Get profile Id
     *
     * @return void
     */
    public function getProfileId()
    {
        return \XLite\Core\Request::getInstance()->profile_id;
    }

    /**
     * Get a list of JS files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        if ($this->getParam(self::PARAM_DISPLAY_WRAPPER)) {
            $list[] = 'form_field/select_country.js';
        }

        return $list;
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'address/style.css';

        return $list;
    }

    /**
     * Return title
     *
     * @return string
     */
    protected function getHead()
    {
        return 'Address';
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'address/' . $this->getParam(self::PARAM_DISPLAY_MODE);
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
            self::PARAM_DISPLAY_MODE => new \XLite\Model\WidgetParam\String(
                'Display mode', self::DISPLAY_MODE_TEXT, false
            ),
            self::PARAM_ADDRESS => new \XLite\Model\WidgetParam\Object(
                'Address object', null, false
            ),
            self::PARAM_DISPLAY_WRAPPER => new \XLite\Model\WidgetParam\Bool(
                'Display wrapper', false, false
            ),
        );
    }

    /**
     * Get default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'address/wrapper.tpl';
    }

    /**
     * Use body template
     *
     * @return boolean
     */
    protected function useBodyTemplate()
    {
        return !$this->getParam(self::PARAM_DISPLAY_WRAPPER);
    }
}
