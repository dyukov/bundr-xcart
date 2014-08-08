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
 * Abstract node
 */
class ANode extends \XLite\View\AView
{
    /**
     * Widget param names
     */
    const PARAM_TITLE         = 'title';
    const PARAM_LINK          = 'link';
    const PARAM_LIST          = 'list';
    const PARAM_BLOCK         = 'block';
    const PARAM_CLASS         = 'className';
    const PARAM_ICON_CLASS    = 'iconClass';
    const PARAM_TARGET        = 'linkTarget';
    const PARAM_EXTRA         = 'extra';
    const PARAM_PERMISSION    = 'permission';
    const PARAM_PUBLIC_ACCESS = 'publicAccess';
    const PARAM_CHILDREN      = 'children';
    const PARAM_SELECTED      = 'selected';
    const PARAM_BLANK_PAGE    = 'blankPage';

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/node.tpl';
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
            self::PARAM_TITLE         => new \XLite\Model\WidgetParam\String('Name', ''),
            self::PARAM_LINK          => new \XLite\Model\WidgetParam\String('Link', ''),
            self::PARAM_BLOCK         => new \XLite\Model\WidgetParam\String('Block', ''),
            self::PARAM_LIST          => new \XLite\Model\WidgetParam\String('List', ''),
            self::PARAM_CLASS         => new \XLite\Model\WidgetParam\String('Class name', ''),
            self::PARAM_ICON_CLASS    => new \XLite\Model\WidgetParam\String('Class name', ''),
            self::PARAM_TARGET        => new \XLite\Model\WidgetParam\String('Target', ''),
            self::PARAM_EXTRA         => new \XLite\Model\WidgetParam\Collection('Additional request params', array()),
            self::PARAM_PERMISSION    => new \XLite\Model\WidgetParam\String('Permission', ''),
            self::PARAM_PUBLIC_ACCESS => new \XLite\Model\WidgetParam\Bool('Public access', false),
            self::PARAM_BLANK_PAGE    => new \XLite\Model\WidgetParam\Bool('Public access', false),
            self::PARAM_CHILDREN      => new \XLite\Model\WidgetParam\Collection('Children', array()),
            self::PARAM_SELECTED      => new \XLite\Model\WidgetParam\Bool('Selected', false),
        );
    }

    /**
     * Return blank page flag (target = "_blank" for the link)
     *
     * @return array
     */
    protected function getBlankPage()
    {
        return $this->getParam(self::PARAM_BLANK_PAGE);
    }

    /**
     * Return children
     *
     * @return array
     */
    protected function getChildren()
    {
        return $this->getParam(self::PARAM_CHILDREN);
    }

    /**
     * Return icon class 
     *
     * @return string
     */
    protected function getIconClass()
    {
        return $this->getParam(self::PARAM_ICON_CLASS);
    }

    /**
     * Check if submenu available for this item
     *
     * @return string
     */
    protected function hasChildren()
    {
        return (
            '' !== $this->getParam(self::PARAM_LIST)
            && 0 < strlen(trim($this->getViewListContent($this->getListName())))
        ) || $this->getChildren();
    }

    /**
     * Check - node is branch but has empty childs list
     *
     * @return boolean
     */
    protected function isEmptyChildsList()
    {
        return '' !== $this->getParam(self::PARAM_LIST)
            && 0 == strlen(trim($this->getViewListContent($this->getListName())))
            && !$this->getChildren();
    }

    /**
     * Return list name
     *
     * @return string
     */
    protected function getLink()
    {
        $link = '#';

        if ('' !== $this->getParam(static::PARAM_LINK)) {
            $link = $this->getParam(static::PARAM_LINK);

        } elseif ('' !== $this->getNodeTarget()) {
            $link = $this->buildURL($this->getNodeTarget(), '', $this->getParam(static::PARAM_EXTRA));
        }

        return $link;
    }

    /**
     * Return the block text
     *
     * @return string
     */
    protected function getBlock()
    {
        return $this->getParam(static::PARAM_BLOCK);
    }

    /**
     * Return if the the link should be active
     * (linked to a current page)
     *
     * @return boolean
     */
    protected function isCurrentPageLink()
    {
        return $this->getParam(static::PARAM_SELECTED);
    }

    /**
     * Return CSS class for the link item
     *
     * @return string
     */
    protected function getCSSClass()
    {
        $class = $this->getParam(static::PARAM_CLASS);

        if ($this->isCurrentPageLink()) {
            $class .= ' active';
        }

        return trim($class);
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && !$this->isEmptyChildsList();
    }

    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    protected function checkACL()
    {
        $auth = \XLite\Core\Auth::getInstance();

        $additionalPermission = $this->getParam(self::PARAM_PERMISSION);

        return parent::checkACL()
            && (
                $this->getParam(self::PARAM_LIST)
                || $this->getParam(self::PARAM_PUBLIC_ACCESS)
                || $auth->isPermissionAllowed(\XLite\Model\Role\Permission::ROOT_ACCESS)
                || ($additionalPermission && $auth->isPermissionAllowed($additionalPermission))
            );
    }

    /**
     * Alias
     *
     * @return string
     */
    protected function getTitle()
    {
        return $this->getParam(static::PARAM_TITLE);
    }

    /**
     * Alias
     *
     * @return string
     */
    protected function getNodeTarget()
    {
        return $this->getParam(static::PARAM_TARGET);
    }
}
