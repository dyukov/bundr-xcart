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
 * Image upload component.
 */
class ImageUpload extends \XLite\View\AView
{
    /*
     * Widget parameters names
     */
    const PARAM_FIELD = 'field';
    const PARAM_ACTION_NAME = 'actionName';
    const PARAM_FORM_NAME = 'formName';
    const PARAM_OBJECT = 'object';

    /**
     * Show delete control
     *
     * @var boolean
     */
    public $showDelete = true;

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'common/image_upload.tpl';
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
            self::PARAM_FIELD       => new \XLite\Model\WidgetParam\String('Field', ''),
            self::PARAM_ACTION_NAME => new \XLite\Model\WidgetParam\String('Action name', ''),
            self::PARAM_FORM_NAME   => new \XLite\Model\WidgetParam\String('Form name', ''),
            self::PARAM_OBJECT      => new \XLite\Model\WidgetParam\Object('Object', null),
        );
    }

    /**
     * Check if object has image
     *
     * @return boolean
     */
    protected function hasImage()
    {
        $field = $this->getParam(self::PARAM_FIELD);
        $method = 'has' . $field;
        $object = $this->getParam(self::PARAM_OBJECT);

        $result = false;

        if (is_object($object) && method_exists($object, $method)) {

            // $method asembled 'has' + $field
            $result = $object->$method();
        }

        return $result;
    }

    /**
     * Check if image is on file system
     *
     * @return void
     */
    protected function isFS()
    {
        return 'F' == $this->getParam(self::PARAM_OBJECT)->get($this->getParam(self::PARAM_FIELD))->getDefaultSource();
    }
}
