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
 * Tooltip widget
 */
class Tooltip extends \XLite\View\AView
{
    /**
     * Widget parameter names
     */
    const PARAM_ID           = 'id';
    const PARAM_TEXT         = 'text';
    const PARAM_CLASS        = 'className';
    const PARAM_CAPTION      = 'caption';
    const PARAM_IS_IMAGE_TAG = 'isImageTag';

    const ATTR_CLASS = 'class';
    const ATTR_ID    = 'id';

    const CAPTION_CSS_CLASS = 'tooltip-caption';

    /**
     * Register files from common repository
     *
     * @return array
     */
    public function getCommonFiles()
    {
        $list = parent::getCommonFiles();

        $list[static::RESOURCE_JS][] = 'js/tooltip.js';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'tooltip.tpl';
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
            static::PARAM_TEXT         => new \XLite\Model\WidgetParam\String('Text to show in tooltip', ''),
            static::PARAM_ID           => new \XLite\Model\WidgetParam\String('ID of element', ''),
            static::PARAM_CLASS        => new \XLite\Model\WidgetParam\String('CSS class for caption', ''),
            static::PARAM_CAPTION      => new \XLite\Model\WidgetParam\String('Caption', ''),
            static::PARAM_IS_IMAGE_TAG => new \XLite\Model\WidgetParam\Bool('Is it shown as image?', true),
        );
    }

    /**
     * Checks if image must be shown
     *
     * @return boolean
     */
    protected function isImageTag()
    {
        return $this->getParam(static::PARAM_IS_IMAGE_TAG);
    }

    /**
     * Define array of attributes
     *
     * @return array
     */
    protected function getAttributes()
    {
        $attrs = array(
            static::ATTR_CLASS => $this->getClass(),
        );

        $attrs += $this->getParam(static::PARAM_ID)
                ? array(self::ATTR_ID => $this->getParam(static::PARAM_ID))
                : array();

        return $attrs;
    }

    /**
     * Return HTML representation for widget attributes
     * TODO - REWORK with AFormField class - same method using
     *
     * @return string
     */
    protected function getAttributesCode()
    {
        $result = '';

        foreach ($this->getAttributes() as $name => $value) {
            $result .= ' ' . $name . '="' . $value . '"';
        }

        return $result;
    }


    /**
     * Define CSS class of caption text
     *
     * @return string
     */
    protected function getClass()
    {
        return static::CAPTION_CSS_CLASS
            . ($this->isImageTag() ? ' fa fa-question-circle ' : ' ')
            . $this->getParam(static::PARAM_CLASS);
    }
}
