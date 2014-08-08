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

namespace XLite\View\Form;

/**
 * Abstract form
 * 
 * To use this form class you MUST redefine getDefaultTarget() method!
 * 
 */
abstract class AForm extends \XLite\View\AView
{
    /**
     * Widget parameter names
     */
    const PARAM_START = 'start';
    const PARAM_END   = 'end';

    const PARAM_FORM_TARGET = 'formTarget';
    const PARAM_FORM_ACTION = 'formAction';
    const PARAM_FORM_NAME   = 'formName';
    const PARAM_FORM_PARAMS = 'formParams';
    const PARAM_FORM_METHOD = 'formMethod';
    const PARAM_CLASS_NAME  = 'className';
    const PARAM_VALIDATION  = 'validationEngine';

    /**
     * Form arguments plain list
     *
     * @var array
     */
    protected $plainList = null;

    /**
     * Validation message
     *
     * @var string
     */
    protected $validationMessage;

    /**
     * Get request data
     *
     * @return mixed
     */
    public function getRequestData()
    {
        $data = null;
        $validator = $this->getValidator();

        try {
            $validator->validate(\XLite\Core\Request::getInstance()->getData());
            $data = $validator->sanitize(\XLite\Core\Request::getInstance()->getData());

        } catch (\XLite\Core\Validator\Exception $exception) {
            $message = static::t($exception->getMessage(), $exception->getLabelArguments());

            if ($exception->isInternal()) {
                \XLite\Logger::getInstance()->log($message, LOG_ERR);

            } else {
                \XLite\Core\Event::invalidElement($exception->getPath(), $message);
            }

            $this->validationMessage
                = ($exception->getPublicName() ? static::t($exception->getPublicName()) . ': ' : '') . $message;
        }

        return $data;
    }

    /**
     * Get validation message
     *
     * @return string
     */
    public function getValidationMessage()
    {
        return $this->validationMessage;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'form/start.tpl';
    }

    /**
     * Open and close form tags
     *
     * @return string
     */
    protected function getTemplate()
    {
        return $this->getParam(self::PARAM_END) ? $this->getEndTemplate() : parent::getTemplate();
    }

    /**
     * Get end form template
     *
     * @return string
     */
    protected function getEndTemplate()
    {
        return 'form/end.tpl';
    }

    /**
     * Required form parameters
     *
     * @return array
     */
    protected function getCommonFormParams()
    {
        return array(
            'target' => $this->getParam(self::PARAM_FORM_TARGET),
            'action' => $this->getParam(self::PARAM_FORM_ACTION),
        );
    }

    /**
     * Return value for the <form action="..." ...> attribute
     *
     * @return string
     */
    protected function getFormAction()
    {
        return $this->buildURL($this->getParam(self::PARAM_FORM_TARGET));
    }

    /**
     * Return list of additional params
     *
     * @return array
     */
    protected function getFormParams()
    {
        $params = array_merge($this->getCommonFormParams(), $this->getParam(self::PARAM_FORM_PARAMS));

        if ($this->hasFormId()) {
            $params[\XLite::FORM_ID] = \XLite::getFormId();
        }

        if ('post' === $this->getParam(self::PARAM_FORM_METHOD)) {
            $this->setReturnURLParam($params);
        }

        return $params;
    }

    /**
     * Check if the form must have the form ID
     * 
     * @return boolean
     */
    protected function hasFormId()
    {
        $class = \XLite\Core\Converter::getControllerClass($this->getParam(static::PARAM_FORM_TARGET));

        return class_exists($class) ? $class::needFormId() : true;
    }
    
    /**
     * Check and (if needed) set the return URL parameter
     *
     * @param array &$params Form params
     *
     * @return void
     */
    protected function setReturnURLParam(array &$params)
    {
        $index = \XLite\Controller\AController::RETURN_URL;

        if (!isset($params[$index])) {
            $params[$index] = \XLite\Core\URLManager::getSelfURI();
        }
    }

    /**
     * JavaScript: this value will be returned on form submit
     * NOTE - this function designed for AJAX easy switch on/off
     *
     * @return string
     */
    protected function getOnSubmitResult()
    {
        return 'true';
    }

    /**
     * JavaScript: default action performed on form submit
     *
     * @return string
     */
    protected function getJSOnSubmitCode()
    {
        return 'return ' . $this->getOnSubmitResult() . ';';
    }

    /**
     * Return default value for the "target" parameter
     *
     * @return string
     */
    abstract protected function getDefaultTarget();

    /**
     * Return default value for the "action" parameter
     *
     * @return string
     */
    protected function getDefaultAction()
    {
        return '';
    }

    /**
     * Return list of the form default parameters
     *
     * @return array
     */
    protected function getDefaultParams()
    {
        return array();
    }

    /**
     * getDefaultFormMethod
     *
     * @return string
     */
    protected function getDefaultFormMethod()
    {
        return 'post';
    }

    /**
     * getDefaultClassName
     *
     * @return string
     */
    protected function getDefaultClassName()
    {
        return null;
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
            self::PARAM_START => new \XLite\Model\WidgetParam\Bool('Is start', true),
            self::PARAM_END   => new \XLite\Model\WidgetParam\Bool('Is end', false),

            self::PARAM_FORM_TARGET => new \XLite\Model\WidgetParam\String(
                'Target', $this->getDefaultTarget()
            ),
            self::PARAM_FORM_ACTION => new \XLite\Model\WidgetParam\String(
                'Action', $this->getDefaultAction()
            ),
            self::PARAM_FORM_NAME => new \XLite\Model\WidgetParam\String(
                'Name', ''
            ),
            self::PARAM_FORM_PARAMS => new \XLite\Model\WidgetParam\Collection(
                'Params', $this->getDefaultParams()
            ),
            self::PARAM_FORM_METHOD => new \XLite\Model\WidgetParam\Set(
                'Request method', $this->getDefaultFormMethod(), array('post', 'get')
            ),
            self::PARAM_CLASS_NAME => new \XLite\Model\WidgetParam\String(
                'Class name', $this->getDefaultClassName()
            ),
            self::PARAM_VALIDATION => new \XLite\Model\WidgetParam\Bool(
                'Apply validation engine', false
            ),
        );
    }

    /**
     * Ability to add the 'enctype="multipart/form-data"' form attribute
     *
     * @return boolean
     */
    protected function isMultipart()
    {
        return false;
    }

    /**
     * Get class name
     *
     * @return string
     */
    protected function getClassName()
    {
        $className = $this->getParam(self::PARAM_CLASS_NAME);

        if ($this->isValidationEngineApplied()) {
            $className = is_null($className)
                ? self::PARAM_VALIDATION
                : $className . ' ' . self::PARAM_VALIDATION;
        }

        return trim($className);
    }

    /**
     * Get validator
     *
     * @return \XLite\Core\Validator\HashArray
     */
    protected function getValidator()
    {
        return new \XLite\Core\Validator\HashArray();
    }

    /**
     * Return current form reference
     *
     * @return \XLite\View\Model\AModel
     */
    protected function getCurrentForm()
    {
        return \XLite\View\Model\AModel::getCurrentForm() ?: $this->getModelForm();
    }

    /**
     * Apply/disable jQuery validation engine for the form fields
     *
     * @return \XLite\View\Model\AModel
     */
    protected function isValidationEngineApplied()
    {
        return $this->getParam(self::PARAM_VALIDATION);
    }

    /**
     * Return form attributes
     *
     * @return array
     */
    protected function getFormAttributes()
    {
        $attrs = array(
            'action'            => $this->getFormAction(),
            'method'            => $this->getParam(static::PARAM_FORM_METHOD),
            'accept-charset'    => 'utf-8',
            'onsubmit'          => 'javascript: ' . $this->getJSOnSubmitCode(),
        );

        if ($this->getClassName()) {
            $attrs['class'] = $this->getClassName();
        }

        if ($this->isMultipart()) {
            $attrs['enctype'] = 'multipart/form-data';
        }

        return $attrs;
    }
}
