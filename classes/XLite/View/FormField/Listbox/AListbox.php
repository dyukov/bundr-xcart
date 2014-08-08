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

namespace XLite\View\FormField\Listbox;

/**
 * Listbox form widget
 */
abstract class AListbox extends \XLite\View\FormField\AFormField
{
    /**
     * Widget param names
     */
    const PARAM_OPTIONS    = 'options';
    const PARAM_LABEL_FROM = 'labelFrom';
    const PARAM_LABEL_TO   = 'labelTo';


    /**
     * Return default options list
     *
     * @return array
     */
    abstract protected function getDefaultOptions();

    /**
     * Return field type
     *
     * @return string
     */
    public function getFieldType()
    {
        return self::FIELD_TYPE_SELECT;
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = $this->getDir() . '/js/listbox.js';

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
        $list[] = $this->getDir() . '/listbox.css';

        return $list;
    }

    /**
     * Register CSS class to use for wrapper block (SPAN) of input field.
     * It is usable to make unique changes of the field.
     *
     * @return string
     */
    public function getWrapperClass()
    {
        return trim(parent::getWrapperClass() . ' input-listbox');
    }

    /**
     * Set widget params
     *
     * @param array $params Handler params
     *
     * @return void
     */
    public function setWidgetParams(array $params)
    {
        parent::setWidgetParams($params);

        if (isset($params['value'])) {
            $this->setValue($params['value']);
        }
    }

    /**
     * Set value
     *
     * @param mixed $value Value to set
     *
     * @return void
     */
    public function setValue($value)
    {
        if (is_object($value) && $value instanceOf \Doctrine\Common\Collections\Collection) {
            $value = $value->toArray();

        } elseif (!is_array($value)) {
            $value = array($value);
        }

        foreach ($value as $k => $v) {
            if (is_object($v) && $v instanceOf \XLite\Model\AEntity) {
                $value[$k] = $v->getUniqueIdentifier();
            }
        }

        parent::setValue($value);
    }

    /**
     * Return field template
     *
     * @return string
     */
    protected function getFieldTemplate()
    {
        return 'listbox.tpl';
    }

    /**
     * Get label container class
     *
     * @return string
     */
    protected function getLabelContainerClass()
    {
        return parent::getLabelContainerClass() . ' input-listbox';
    }

    /**
     * Get value container class
     *
     * @return string
     */
    protected function getValueContainerClass()
    {
        return parent::getValueContainerClass() . ' input-listbox';
    }

    /**
     * Return label for 'from' selector
     *
     * @return string
     */
    protected function getLabelFrom()
    {
        return $this->getParam(self::PARAM_LABEL_FROM);
    }

    /**
     * Return label for 'to' selector
     *
     * @return string
     */
    protected function getLabelTo()
    {
        return $this->getParam(self::PARAM_LABEL_TO);
    }

    /**
     * getOptions
     *
     * @return array
     */
    protected function getOptions()
    {
        return $this->getParam(self::PARAM_OPTIONS);
    }

    /**
     * getOptions
     *
     * @return array
     */
    protected function getOptionsFrom()
    {
        return $this->getSpecificOptions();
    }

    /**
     * getOptions
     *
     * @return array
     */
    protected function getOptionsTo()
    {
        return $this->getSpecificOptions(false);
    }

    /**
     * Get specific options list for 'from' and for 'to' lists
     *
     * @param boolean $from Flag: is need to get options for 'from' list
     *
     * @return array
     */
    protected function getSpecificOptions($from = true)
    {
        $options = $this->getOptions();
        $values = $this->getValue();

        $keys = is_array($values) ? $values : array($values);

        $result = \Includes\Utils\ArrayManager::filterByKeys($options, $keys, $from);

        return $result;
    }

    /**
     * Checks if the list is empty
     *
     * @return boolean
     */
    protected function isListEmpty()
    {
        return 0 >= count($this->getOptions());
    }

    /**
     * Get default label for 'From' selector
     *
     * @return string
     */
    protected function getDefaultLabelFrom()
    {
        return 'Unset';
    }

    /**
     * Get default label for 'To' selector
     *
     * @return string
     */
    protected function getDefaultLabelTo()
    {
        return 'Set';
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
            self::PARAM_OPTIONS => new \XLite\Model\WidgetParam\Collection(
                'Options', $this->getDefaultOptions(), false
            ),
            self::PARAM_LABEL_FROM => new \XLite\Model\WidgetParam\String(
                'Label FROM', $this->getDefaultLabelFrom(), false
            ),
            self::PARAM_LABEL_TO => new \XLite\Model\WidgetParam\String(
                'Label TO', $this->getDefaultLabelTo(), false
            ),
        );
    }
}
