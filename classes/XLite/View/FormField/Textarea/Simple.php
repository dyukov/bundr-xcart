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

namespace XLite\View\FormField\Textarea;

/**
 * Textarea
 */
class Simple extends \XLite\View\FormField\Textarea\ATextarea
{
    /**
     * Widget param names
     */
    const PARAM_MIN_HEIGHT = 'maxWidth';
    const PARAM_MAX_HEIGHT = 'maxHeight';

    /**
     * Define widget params
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_MIN_HEIGHT  => new \XLite\Model\WidgetParam\Int('Min. height', 0),
            static::PARAM_MAX_HEIGHT => new \XLite\Model\WidgetParam\Int('Max. height', 0),
        );
    }

    /**
     * Register files from common repository
     *
     * @return array
     */
    protected function getCommonFiles()
    {
        $list = parent::getCommonFiles();

        if ($this->getParam(static::PARAM_MAX_HEIGHT)) {
            $list[static::RESOURCE_JS][] = 'js/jquery.textarea-expander.js';
        }

        return $list;
    }

    /**
     * Return field template
     *
     * @return string
     */
    protected function getFieldTemplate()
    {
        return 'textarea.tpl';
    }

    /**
     * setCommonAttributes
     *
     * @param array $attrs Field attributes to prepare
     *
     * @return array
     */
    protected function setCommonAttributes(array $attrs)
    {
        $attrs = parent::setCommonAttributes($attrs);

        if ($this->getParam(static::PARAM_MAX_HEIGHT)) {

            if ($this->getParam(static::PARAM_MIN_HEIGHT)) {
                $attrs['data-min-size-height'] = $this->getParam(static::PARAM_MIN_HEIGHT);
            }

            if ($this->getParam(static::PARAM_MAX_HEIGHT)) {
                $attrs['data-max-size-height'] = $this->getParam(static::PARAM_MAX_HEIGHT);
            }

            if (empty($attrs['class'])) {
                $attrs['class'] = '';
            }

            $attrs['class'] = trim($attrs['class'] . ' resizeble-txt');
        }

        unset($attrs['value']);

        return $attrs;
    }
}
