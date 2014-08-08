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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\View;

/**
 * Controller main widget
 */
class Controller extends \XLite\View\AView
{
    /**
     * Content of the currnt page
     * NOTE: this is a text, so it's not passed by reference; do not wrap it into a getter (or pass by reference)
     * NOTE: until it's not accessing via the function, do not change its access modifier
     *
     * @var string
     */
    public static $bodyContent = null;

    /**
     * __construct
     *
     * @param array  $params          Widget params OPTIONAL
     * @param string $contentTemplate Central area template OPTIONAL
     *
     * @return void
     */
    public function __construct(array $params = array(), $contentTemplate = null)
    {
        parent::__construct($params);

        $this->template = $contentTemplate;
    }

    /**
     * Show current page and, optionally, footer
     *
     * @param string $template Template file name OPTIONAL
     *
     * @return void
     */
    public function display($template = null)
    {
        if (!$this->isSilent()) {
            $this->displayPage($template);
        }

        if ($this->isDumpStarted()) {
            $this->refreshEnd();
        }

        $this->postprocess();
    }

    /**
     * Check - first sidebar is visible or not
     *
     * @return boolean
     */
    public static function isSidebarFirstVisible()
    {
        return \XLite::isAdminZone() ? static::isAdminSidebarFirstVisible() : static::isCustomerSidebarFirstVisible();
    }

    /**
     * Check - second sidebar is visible or not
     *
     * @return boolean
     */
    public static function isSidebarSecondVisible()
    {
        return false;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->isAJAXCenterRequest() ? 'center_top.tpl' : 'body.tpl';
    }

    /**
     * Check - current request is AJAX background request for page center or not
     *
     * @return boolean
     */
    protected function isAJAXCenterRequest()
    {
        return $this->isAJAX() && \XLite\Core\Request::getInstance()->only_center;
    }

    /**
     * CSS classes which are defined in the defineBodyClasses() method are assembled into the one string
     *
     * @return string
     *
     * @see \XLite\View\Content::defineBodyClasses()
     */
    protected function getBodyClasses()
    {
        return implode(' ', $this->defineBodyClasses());
    }

    /**
     * The layout defines the specific CSS classes for the 'body' tag
     * The body CSS classes can define:
     *
     * AREA: area-a / area-c
     * SKINS that are added to this interface: skin-<skin1>, skin-<skin2>, ...
     * TARGET : target-<target_name>
     * Sidebars: one-sidebar | two-sidebars | no-sidebars | sidebar-first | sidebar-second
     *
     * @return array Array of CSS classes to apply to the 'body' tag
     */
    protected function defineBodyClasses()
    {
        $classes = array(
            'area-' . (\XLite::isAdminZone() ? 'a' : 'c'),
        );

        foreach (array_reverse(\XLite\Core\Layout::getInstance()->getSkins()) as $skin) {
            $classes[] = 'skin-' . $this->prepareCSSClass($skin);
        }

        $classes[] = \XLite\Core\Auth::getInstance()->isLogged() ? 'authorized' : 'unauthorized';

        $classes[] = 'target-' . str_replace('_', '-', \XLite\Core\Request::getInstance()->target);

        $first = $this->isSidebarFirstVisible();
        $second = $this->isSidebarSecondVisible();

        if ($first && $second) {
            $classes[] = 'two-sidebars';

        } elseif ($first || $second) {
            $classes[] = 'one-sidebar';

        } else {
            $classes[] = 'no-sidebars';
        }

        if ($first) {
            $classes[] = 'sidebar-first';
        }

        if ($second) {
            $classes[] = 'sidebar-second';
        }

        $classes = \XLite::getController()->defineBodyClasses($classes);

        return $classes;
    }

    /**
     * Before using the CSS class in the 'class' attribute it must be prepared to be valid
     * The restricted symbols are changed to '-'
     *
     * @param  string $class CSS class name to be prepared
     *
     * @return string
     *
     * @see \XLite\View\AView::defineBodyClasses()
     */
    protected function prepareCSSClass($class)
    {
        return preg_replace('/[^a-z0-9_-]+/Sis', '-', $class);
    }

    /**
     * Check - first sidebar is visible or not (in admin interface)
     *
     * @return boolean
     */
    protected static function isAdminSidebarFirstVisible()
    {
        return false;
    }

    /**
     * Check - first sidebar is visible or not (in customer interface)
     *
     * @return boolean
     */
    protected static function isCustomerSidebarFirstVisible()
    {
        return !in_array(
            \XLite\Core\Request::getInstance()->target,
            static::getSidebarFirstHiddenTargets()
        );
    }

    /**
     * Define the pages where first sidebar will be hidden.
     * By default we hide it on:
     *      product page,
     *      cart page,
     *      checkout page
     *      checkout success (invoice) page
     *      payment page
     *
     * @return array
     */
    protected static function getSidebarFirstHiddenTargets()
    {
        return array(
            'cart',
            'product',
            'checkout',
            'checkoutPayment',
            'checkoutSuccess',
        );
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
            self::PARAM_SILENT       => new \XLite\Model\WidgetParam\Bool('Silent', false),
            self::PARAM_DUMP_STARTED => new \XLite\Model\WidgetParam\Bool('Dump started', false)
        );
    }

    /**
     * isSilent
     *
     * @return boolean
     */
    protected function isSilent()
    {
        return $this->getParam(self::PARAM_SILENT);
    }

    /**
     * isDumpStarted
     *
     * @return boolean
     */
    protected function isDumpStarted()
    {
        return $this->getParam(self::PARAM_DUMP_STARTED);
    }

    /**
     * getContentWidget
     *
     * @return \XLite\View\AView
     */
    protected function getContentWidget()
    {
        return $this->getWidget(array(\XLite\View\AView::PARAM_TEMPLATE => $this->template), '\XLite\View\Content');
    }

    /**
     * prepareContent
     *
     * @return void
     */
    protected function prepareContent()
    {
        self::$bodyContent = $this->getContentWidget()->getContent();
    }

    /**
     * Return TRUE  if widget must be displayed inside CMS content.
     * Return FALSE if standalone display mode of LC is used.
     *
     * @return boolean
     */
    protected function useDefaultDisplayMode()
    {
        return $this->isExported();
    }

    /**
     * displayPage
     *
     * @param string $template Template file name OPTIONAL
     *
     * @return void
     */
    protected function displayPage($template = null)
    {
        if ($this->useDefaultDisplayMode()) {
            // Display widget content inside some CMS content
            $this->getContentWidget()->display($template);

        } else {
            // Display widget in standalone display mode
            $this->prepareContent();

            parent::display($template);
        }
    }

    /**
     * refreshEnd
     *
     * @return void
     */
    protected function refreshEnd()
    {
        func_refresh_end();
    }

    /**
     * Get body class
     *
     * @return string
     */
    protected function getBodyClass()
    {
        return implode(' ', $this->defineBodyClasses());
    }

    /**
     * Return common data to send to JS
     *
     * @return array
     */
    protected function getCommonJSData()
    {
        return $this->defineCommonJSData();
    }
}
