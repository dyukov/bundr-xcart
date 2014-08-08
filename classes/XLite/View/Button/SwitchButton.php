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
 * Switch button (register two onclick callbacks JS functions)
 */
class SwitchButton extends \XLite\View\Button\AButton
{
    /**
     * Several inner constants
     */
    const JS_SCRIPT = 'button/js/switch-button.js';
    const SWITCH_CSS_FILE = 'button/css/switch-button.css';

    /**
     * Widget parameters to use
     */
    const PARAM_FIRST  = 'first';
    const PARAM_SECOND = 'second';

    /**
     * Get a list of JavaScript files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = self::JS_SCRIPT;

        return $list;
    }

    /**
     * Return CSS files list
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = self::SWITCH_CSS_FILE;

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'button/switch-button.tpl';
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
            self::PARAM_FIRST  => new \XLite\Model\WidgetParam\String('First callback', ''),
            self::PARAM_SECOND => new \XLite\Model\WidgetParam\String('Second callback', ''),
        );
    }

    /**
     * Return JS callbacks to use with onclick event
     *
     * @return array
     */
    protected function getCallbacks()
    {
        return array(
            'callbacks' => array (
                'first'  => $this->getParam(self::PARAM_FIRST),
                'second' => $this->getParam(self::PARAM_SECOND),
            ),
        );
    }
}
