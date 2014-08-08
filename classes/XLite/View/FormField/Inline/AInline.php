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

namespace XLite\View\FormField\Inline;

/**
 * Abstract inline form-field
 */
abstract class AInline extends \XLite\View\AView
{
    const PARAM_ENTITY       = 'entity';
    const PARAM_ITEMS_LIST   = 'itemsList';
    const PARAM_FIELD_NAME   = 'fieldName';
    const PARAM_FIELD_PARAMS = 'fieldParams';
    const PARAM_EDIT_ONLY    = 'editOnly';

    const FIELD_NAME   = 'name';
    const FIELD_PARAMS = 'parameters';
    const FIELD_CLASS  = 'class';
    const FIELD_LABEL  = 'label';

    /**
     * Form fields 
     * 
     * @var array
     */
    protected $fields;

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'form_field/inline/style.css';

        return $list;
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'form_field/inline/controller.js';

        return $list;
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
            static::PARAM_ENTITY       => new \XLite\Model\WidgetParam\Object('Entity', null, false, 'XLite\Model\AEntity'),
            static::PARAM_ITEMS_LIST   => new \XLite\Model\WidgetParam\Object('Items list', null, false, 'XLite\View\ItemsList\Model\AModel'),
            static::PARAM_FIELD_NAME   => new \XLite\Model\WidgetParam\String('Field name', ''),
            static::PARAM_FIELD_PARAMS => new \XLite\Model\WidgetParam\Collection('Field parameters list', array()),
            static::PARAM_EDIT_ONLY    => new \XLite\Model\WidgetParam\Bool('Edit only flag', false),

        );
    }

    /**
     * Get entity
     *
     * @return \XLite\Model\AEntity
     */
    protected function getEntity()
    {
        return $this->getParam(static::PARAM_ENTITY);
    }

    /**
     * Get edit only flag 
     *
     * @return boolean
     */
    protected function getEditOnly()
    {
        return $this->getParam(static::PARAM_EDIT_ONLY);
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'form_field/inline.tpl';
    }

    /**
     * Check - field is editable or not
     * 
     * @return boolean
     */
    protected function isEditable()
    {
        return $this->getEditOnly() || ($this->getEntity() && $this->getEntity()->isPersistent());
    }

    /**
     * Check - field is editable or not
     *
     * @return boolean
     */
    protected function hasSeparateView()
    {
        return !$this->getEditOnly();
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && ($this->isEditable() || $this->hasSeparateView());
    }

    // {{{ Content helpers

    /**
     * Get container class 
     * 
     * @return string
     */
    protected function getContainerClass()
    {
        $parts = explode('\\', get_class($this->getParam(static::PARAM_ENTITY)));
        $class = strtolower(array_pop($parts));

        return 'inline-field'
            . ($this->isEditable() ? ' editable' : '')
            . ($this->hasSeparateView() ? ' has-view' : '')
            . (' ' . $class . '-' . $this->getParam(static::PARAM_FIELD_NAME));
    }

    /**
     * Get view template 
     * 
     * @return string
     */
    protected function getViewTemplate()
    {
        return 'form_field/inline/view.tpl';
    }

    /**
     * Get field template
     *
     * @return string
     */
    protected function getFieldTemplate()
    {
        return 'form_field/inline/field.tpl';
    }

    /**
     * Get view value
     *
     * @param array $field Field
     *
     * @return mixed
     */
    protected function getViewValue(array $field)
    {
        $method = 'getViewValue' . ucfirst($field['field'][static::FIELD_NAME]);

        if (method_exists($this, $method)) {

            // $method assembled from 'getViewValue' + field short name
            $result = $this->$method($field);

        } else {
            $value = $field['widget']->getValue();
            $result = 0 == strlen(strval($value)) ? '&nbsp;' : $value;
        }

        return $result;
    }

    /**
     * Get field 
     * 
     * @param string $name Feild name
     *  
     * @return array
     */
    protected function getField($name)
    {
        $list = $this->getFields();

        return isset($list[$name]) ? $list[$name] : null;
    }

    /**
     * Get field widget 
     * 
     * @param string $name Field name
     *  
     * @return \XLite\View\FormField\AFormField
     */
    protected function getFieldWidget($name)
    {
        $field = $this->getField($name);

        return $field ? $field['widget'] : null;
    }

    /**
     * Get field class name 
     * 
     * @param array $field Field
     *  
     * @return string
     */
    protected function getFieldClassName(array $field)
    {
        return 'subfield subfield-' . $field['field'][static::FIELD_NAME];
    }

    // }}}

    // {{{ Form field

    /**
     * Define fields 
     * 
     * @return array
     */
    abstract protected function defineFields();

    /**
     * Set value from request
     *
     * @param array $data Data OPTIONAL
     * @param mixed $key  Row key OPTIONAL
     *
     * @return void
     */
    public function setValueFromRequest(array $data = array(), $key = null)
    {
        $data = $data ?: \XLite\Core\Request::getInstance()->getData();

        foreach ($this->getFields() as $field) {

            $method = 'setValue' . ucfirst($field['field'][static::FIELD_NAME]);
            if (method_exists($this, $method)) {

                // $method assemble from 'setValue' + field name
                $this->$method($field, $data, $key);

            } else {
                $this->setFieldValue($field, $data, $key);
            }
        }
    }

    /**
     * Validate
     *
     * @return array
     */
    public function validate()
    {
        $result = array(true, null);

        foreach ($this->getFields() as $field) {
            $result = $this->validateField($field);
            
            if (!$result[0]) {
                break;
            }
        }

        return $result;
    }

    /**
     * Save value
     *
     * @return void
     */
    public function saveValue()
    {
        foreach ($this->getFields() as $field) {
            $method = 'saveValue' . ucfirst($field['field'][static::FIELD_NAME]);
            if (method_exists($this, $method)) {

                // $method assemble from 'saveValue' + field name
                $this->$method($field);

            } else {
                $value = $field['widget']->getValue();
                $value = $this->preprocessValueBeforeSave($value);

                $method = 'preprocessValueBeforeSave' . ucfirst($field['field'][static::FIELD_NAME]);
                if (method_exists($this, $method)) {

                   // $method assemble from 'preprocessValueBeforeSave' + field name
                    $value = $this->$method($value);
                }

                $method = 'set' . ucfirst($field['field'][static::FIELD_NAME]);

                // $method assemble from 'set' + field name
                $this->getEntity()->$method($value);
            }
        }
    }

    /**
     * Get field label 
     * 
     * @return string
     */
    public function getLabel()
    {
        return \XLite\Core\Translation::lbl(ucfirst($this->getParam(static::PARAM_FIELD_NAME)));
    }

    /**
     * Get fields 
     * 
     * @return array
     */
    protected function getFields()
    {
        if (!isset($this->fields)) {
            $this->fields = array();
            foreach ($this->defineFields() as $name => $field) {
                if (isset($field[static::FIELD_CLASS])) {
                    $field[static::FIELD_NAME] = isset($field[static::FIELD_NAME]) ? $field[static::FIELD_NAME] : $name;
                    $field[static::FIELD_PARAMS] = $this->getFieldParams($field);

                    $this->fields[$name] = array(
                        'field'  => $field,
                        'widget' => $this->getWidget($field[static::FIELD_PARAMS], $field[static::FIELD_CLASS]),
                    );
                }
            }
        }

        return $this->fields;
    }

    /**
     * Get field widgets
     *
     * @return array
     */
    protected function getFieldWidgets()
    {
        $list = array();

        foreach ($this->getFields() as $name => $field) {
            $list[$name] = $field['widget'];
        }

        return $list;
    }

    /**
     * Get initial field parameters
     *
     * @param array $field Field data
     * 
     * @return array
     */
    protected function getFieldParams(array $field)
    {
        $parts = $this->getNameParts($field);
        $label = isset($field[static::FIELD_LABEL]) ? $field[static::FIELD_LABEL] : $field[static::FIELD_NAME];

        $list = array(
            'fieldOnly' => true,
            'nameParts' => $parts,
            'fieldName' => array_shift($parts) . ($parts ? ('[' . implode('][', $parts) . ']') : ''),
            'value'     => $this->getFieldEntityValue($field),
            'label'     => \XLite\Core\Translation::lbl($label),
        );

        if (!empty($field[static::FIELD_PARAMS]) && is_array($field[static::FIELD_PARAMS])) {
            $list = array_merge($list, $field[static::FIELD_PARAMS]);
        }

        return array_merge($list, $this->getParam(static::PARAM_FIELD_PARAMS));
    }

    /**
     * Get field name parts 
     * 
     * @param array $field Field
     *  
     * @return array
     */
    protected function getNameParts(array $field)
    {
        if (!$this->getEntity()->getUniqueIdentifier()) {
            $parts = array(
                $this->getParam(static::PARAM_ITEMS_LIST)->getCreateDataPrefix(),
                0,
                $field[static::FIELD_NAME],
            );

        } elseif (!$this->getParam(static::PARAM_ITEMS_LIST)) {
            $parts = array(
                $field[static::FIELD_NAME],
                $this->getEntity()->getUniqueIdentifier(),
            );
            
        } else {
            $parts = array(
                $this->getParam(static::PARAM_ITEMS_LIST)->getDataPrefix(),
                $this->getEntity()->getUniqueIdentifier(),
                $field[static::FIELD_NAME],
            );

        }
 
        return $parts;
    }

    /**
     * Get field value from entity
     * 
     * @param array $field Field
     *  
     * @return mixed
     */
    protected function getFieldEntityValue(array $field)
    {
        $method = 'get' . ucfirst($field[static::FIELD_NAME]);

        // $method assembled from 'get' + field short name
        return $this->getEntity()->$method();
    }

    /**
     * Set field value 
     * 
     * @param array $field Field
     * @param array $data  Data
     * @param mixed $key   Row key OPTIONAL
     *  
     * @return void
     */
    protected function setFieldValue(array $field, array $data, $key = null)
    {
        $this->transferValueToField($field, $this->isolateFieldValue($field, $data, $key));
    }

    /**
     * Isolate field value 
     * 
     * @param array $field Field info
     * @param array $data  Data
     * @param mixed $key   Row key OPTIONAL
     *  
     * @return mixed
     */
    protected function isolateFieldValue(array $field, array $data, $key = null)
    {
        $found = true;

        foreach ($field['field'][static::FIELD_PARAMS]['nameParts'] as $part) {

            if (0 === $part && isset($key)) {
                $part = $key;
            }

            if (isset($data[$part])) {
                $data =& $data[$part];

            } else {
                $found = false;
                break;
            }
        }

        return $found ? $data : null;
    }

    /**
     * Transfer isolated value to field 
     * 
     * @param array $field Filed info
     * @param mixed $value Value
     *  
     * @return void
     */
    protected function transferValueToField(array $field, $value)
    {
        if (isset($value)) {
            $field['widget']->setValue($value);
        }
    }

    /**
     * Preprocess value before save
     *
     * @param mixed $value Value
     *
     * @return mixed
     */
    protected function preprocessValueBeforeSave($value)
    {
        return $value;
    }

    /**
     * Validate field 
     * 
     * @param array $field Feild info
     *  
     * @return array
     */
    protected function validateField(array $field)
    {
        $method = 'validate' . ucfirst($field['field'][static::FIELD_NAME]);

        return method_exists($this, $method)
            ? $this->$method($field)
            : $field['widget']->validate();
    }

    // }}}

}
