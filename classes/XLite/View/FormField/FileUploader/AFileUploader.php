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

namespace XLite\View\FormField\FileUploader;

/**
 * Abstract file uploader
 */
abstract class AFileUploader extends \XLite\View\FormField\AFormField
{
    /**
     * Widget param names
     */
    const PARAM_MULTIPLE    = 'multiple';
    const PARAM_MAX_WIDTH   = 'maxWidth';
    const PARAM_MAX_HEIGHT  = 'maxHeight';

    /**
     * Return field type
     *
     * @return string
     */
    public function getFieldType()
    {
        return self::FIELD_TYPE_FILE;
    }

    /**
     * Return 'isImage' flag
     *
     * @return boolean
     */
    abstract protected function isImage();

    /**
     * Return max width
     *
     * @return integer
     */
    protected function getMaxWidth()
    {
        return $this->getParam(static::PARAM_MAX_WIDTH);
    }

    /**
     * Return max height
     *
     * @return integer
     */
    protected function getMaxHeight()
    {
        return $this->getParam(static::PARAM_MAX_HEIGHT);
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
            static::PARAM_MULTIPLE    => new \XLite\Model\WidgetParam\Bool('Multiple', false),
            static::PARAM_MAX_WIDTH   => new \XLite\Model\WidgetParam\Int('Max. width', 122),
            static::PARAM_MAX_HEIGHT  => new \XLite\Model\WidgetParam\Int('Max. height', 122),
        );
    }

    /**
     * Return field template
     *
     * @return string
     */
    protected function getFieldTemplate()
    {
        return $this->getParam(static::PARAM_MULTIPLE)
            ? 'file_uploader/multiple.tpl'
            : 'file_uploader/single.tpl';
    }

    /**
     * getCommonAttributes
     *
     * @return array
     */
    protected function getCommonAttributes()
    {
        $list = parent::getCommonAttributes();

        if ($this->getParam(static::PARAM_MULTIPLE)) {
            $list['multiple'] = $this->getParam(static::PARAM_MULTIPLE);
        }
        $list['max_width'] = $this->getParam(static::PARAM_MAX_WIDTH);
        $list['max_height'] = $this->getParam(static::PARAM_MAX_HEIGHT);

        return $list;
    }

    /**
     * Return HTML representation for widget attributes
     *
     * @return string
     */
    protected function getDataCode()
    {
        $result = '';

        foreach ($this->getAttributes() as $name => $value) {
            if ('class' != $name) {
                $result .= ' data-' . $name . '="' . func_htmlspecialchars($value) . '"';
            }
        }

        return $result;
    }
}
