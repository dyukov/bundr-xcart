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

namespace XLite\View\Menu\Admin;

/**
 * Side-box menu
 */
class SideBox extends \XLite\View\Menu\Admin\AAdmin
{
    /**
     * Head 
     *
     * @return string
     */
    protected $head = '';

    /**
     * Selected title 
     *
     * @return string
     */
    protected $title;

    /**
     * Get index
     *
     * @return string
     */
    protected function getIndex()
    {
        return null;
    }

    /**
     * Define items
     *
     * @return array
     */
    protected function defineItems()
    {
        $menu = \XLite\View\Menu\Admin\TopLinks::getInstance()->getItems();

        if (isset($menu[$this->getIndex()])) {
            $items = $menu[$this->getIndex()]->getParam(\XLite\View\Menu\Admin\ANode::PARAM_CHILDREN);
            $this->head = $menu[$this->getIndex()]->getParam(\XLite\View\Menu\Admin\ANode::PARAM_TITLE);

        } else {
            $items = array();
        }

        return $items;
    }

    /**
     * Return unallowed targets 
     *
     * @return array
     */
    protected function getUnallowedTargets()
    {
        return array();
    }

    /**
     * Prepare items
     *
     * @param array $items Items
     *
     * @return array
     */
    protected function prepareItems($items)
    {
        $result = array();

        foreach ($items as $key => $widget) {
            if (
                $widget->getParam($widget::PARAM_TARGET)
                && !in_array($widget->getParam($widget::PARAM_TARGET), $this->getUnallowedTargets())
            ) {
                $result[$key] = array(
                    'title'      => $widget->getParam($widget::PARAM_TITLE),
                    'link'       => $this->buildURL(
                        $widget->getParam($widget::PARAM_TARGET),
                        '',
                        $widget->getParam($widget::PARAM_EXTRA)
                    ),
                    'selected'   => $widget->getParam($widget::PARAM_SELECTED),
                );
            }
        }

        return $result;
    }

    /**
     * Mark selected 
     *
     * @param array $items Items
     *
     * @return array
     */
    protected function markSelected($items)
    {
        return $items;
    }

   /**
     * Return title
     *
     * @return string
     */
    protected function getHead()
    {
        return $this->head;
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = $this->getDir() . '/style.css';

        return $list;
    }

    /**
     * Return widget directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'side_box';
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/body.tpl';
    }

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return 'side-box side-menu';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->isItemsActive();
    }

    /**
     * Check - items is active or not
     *
     * @return boolean
     */
    protected function isItemsActive()
    {
        $selected = false;

        foreach ($this->getItems() as $key => $item) {
            if ($item['selected']) {
                $selected = true;
                $this->title = $this->preprocessTitle($key, $item['title']);

                break;
            }
        }

        return $selected;
    }

    /**
     * Preprocess page title
     *
     * @param string $key   Menu item key
     * @param string $title Default page title
     *
     * @return string
     */
    protected function preprocessTitle($key, $title)
    {
        $methodName = 'prepareTitle' . \Includes\Utils\Converter::convertToCamelCase($key);

        if (method_exists($this, $methodName)) {
            $title = $this->{$methodName}($title);
        }

        return $title;
    }

    /**
     * Adjust title of payment method settings page
     *
     * @param string $title Page title
     *
     * @return string
     */
    protected function prepareTitlePaymentSettings($title)
    {
        if ('payment_method' == $this->getTarget()) {

            if (\XLite\Core\Request::getInstance()->method_id) {
                $pm = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
                    ->find(\XLite\Core\Request::getInstance()->method_id);

                if ($pm) {
                    $title .= ' :: ' . $pm->getName();
                }
            }
        }

        return $title;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->isVisible()
            ? $this->title
            : null;
    }

    /**
     * Get item class
     *
     * @param array $item Item
     *
     * @return string
     */
    protected function getItemClass(array $item)
    {
        $classes = array();

        if (1 == $this->itemArrayPointer) {
            $classes[] = 'first';
        }

        if ($this->itemArraySize == $this->itemArrayPointer) {
            $classes[] = 'last';
        }

        if (!empty($item['selected'])) {
            $classes[] = 'selected';
        }

        return implode(' ', $classes);
    }
}
