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
 * Tabber is a component allowing to organize your dialog into pages and
 * switch between the page using Tabs at the top.
 */
class Tabber extends \XLite\View\AView
{
    /*
     * Widget parameters names
     */
    const PARAM_BODY      = 'body';
    const PARAM_SWITCH    = 'switch';
    const PARAM_TAB_PAGES = 'tabPages';

    /**
     * Lazy initialization cache
     */
    protected $pages = null;
    
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'common/tabber.tpl';
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
            self::PARAM_BODY      => new \XLite\Model\WidgetParam\String('Body template file', '', false),
            self::PARAM_SWITCH    => new \XLite\Model\WidgetParam\String('Switch', 'page', false),
            self::PARAM_TAB_PAGES => new \XLite\Model\WidgetParam\String('Name of function that returns tab pages', 'getTabPages', false)

        );
    }

    /**
     * Get prepared pages array for tabber
     *
     * @return void
     */
    protected function getTabberPages()
    {
        if (is_null($this->pages)) {
            $this->pages = array();
            $url = $this->get('url');
            $switch = $this->getParam(self::PARAM_SWITCH);
            $functionName = $this->getParam(self::PARAM_TAB_PAGES);

            // $functionName - from PARAM_TAB_PAGES parameter
            $dialogPages = \XLite::getController()->$functionName();

            if (is_array($dialogPages)) {
                foreach ($dialogPages as $page => $title) {
                    $p = new \XLite\Base();
                    $pageURL = preg_replace('/' . $switch . '=(\w+)/', $switch . '=' . $page, $url);
                    $p->set('url', $pageURL);
                    $p->set('title', $title);
                    $pageSwitch = sprintf($switch . '=' . $page);
                    $p->set('selected', (preg_match('/' . preg_quote($pageSwitch) . '(\Z|&)/Ss', $url)));
                    $this->pages[] = $p;
                }
            }

            // if there is only one tab page, set it as a seleted with the default URL
            if (1 == count($this->pages) || 'default' === $this->getPage()) {
                $this->pages[0]->set('selected', $url);
            }

        }
        
        return $this->pages;
    }

    /**
     * Define the specific CSS class for the tab page
     * 
     * @return string
     */
    protected function getTabPageCSS()
    {
        return 'tab-pages-' . count($this->getTabberPages());
    }
}
