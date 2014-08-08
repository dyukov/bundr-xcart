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
 * Link as button
 */
class Link extends \XLite\View\Button\AButton
{
    /**
     * Widget parameter names
     */
    const PARAM_LOCATION = 'location';
    const PARAM_JS_CODE  = 'jsCode';
    const PARAM_BLANK    = 'blank';

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_LOCATION => new \XLite\Model\WidgetParam\String('Redirect to', $this->getDefaultLocation(), true),
            self::PARAM_JS_CODE  => new \XLite\Model\WidgetParam\String('JS code', null, true),
            self::PARAM_BLANK    => new \XLite\Model\WidgetParam\Bool('Open in new window', false),
        );
    }

    /**
     * JavaScript: this code will be used by default
     *
     * @return string
     */
    protected function getDefaultJSCode()
    {
        return $this->getParam(self::PARAM_BLANK)
            ? 'window.open(\'' . $this->getLocationURL() . '\');'
            : 'self.location = \'' . $this->getLocationURL() . '\';';
    }

    /**
     * Defines the default location path
     *
     * @return null|string
     */
    protected function getDefaultLocation()
    {
        return null;
    }

    /**
     * We make the full location path for the provided URL
     *
     * @return string
     */
    protected function getLocationURL()
    {
        return \XLite::getInstance()->getShopURL($this->getParam(static::PARAM_LOCATION));
    }

    /**
     * JavaScript: return specified (or default) JS code to execute
     *
     * @return string
     */
    protected function getJSCode()
    {
        return $this->getParam(self::PARAM_JS_CODE) ? $this->getParam(self::PARAM_JS_CODE) : $this->getDefaultJSCode();
    }

    /**
     * Get attributes
     *
     * @return array
     */
    protected function getAttributes()
    {
        $list = parent::getAttributes();

        return array_merge($list, $this->getLinkAttributes());
    }

    /**
     * Onclick specific attribute is added
     *
     * @return array
     */
    protected function getLinkAttributes()
    {
        return array(
            'onclick' => 'javascript: ' . $this->getJSCode(),
        );
    }
}
