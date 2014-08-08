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
 * Subcategories list
 *
 * @ListChild (list="center.bottom", zone="customer", weight="100")
 */
class Subcategories extends \XLite\View\Dialog
{
    /**
     * Widget parameter names
     */
    const PARAM_DISPLAY_MODE = 'displayMode';
    const PARAM_ICON_MAX_WIDTH = 'iconWidth';
    const PARAM_ICON_MAX_HEIGHT = 'iconHeight';

    /**
     * Allowed display modes
     */
    const DISPLAY_MODE_LIST  = 'list';
    const DISPLAY_MODE_ICONS = 'icons';
    const DISPLAY_MODE_HIDE  = 'hide';

    /*
     * Default display mode
     */
    const DEFAULT_DISPLAY_MODE = self::DISPLAY_MODE_ICONS;

    /**
     * Display modes
     *
     * @var array
     */
    protected $displayModes = array(
        self::DISPLAY_MODE_LIST  => 'List',
        self::DISPLAY_MODE_ICONS => 'Icons',
        self::DISPLAY_MODE_HIDE  => 'Hide',
    );

    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();
        $result[] = 'main';
        $result[] = 'category';

        return $result;
    }

    /**
     * Return list of required CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        if (!\XLite::isAdminZone()) {
            $list[] = 'common/grid-list.css';
        }

        return $list;
    }

    /**
     * Return title
     *
     * @return string
     */
    protected function getHead()
    {
        return null;
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        if ('main' == \XLite\Core\Request::getInstance()->target) {
            $dir = 'subcategories/' . $this->getRootCategoryDisplayMode();
        } else {
            $dir = 'subcategories/' . $this->getParam(self::PARAM_DISPLAY_MODE);
        }

        return $dir;
    }

    /**
     * Get widget display mode
     *
     * @return string
     */
    protected function getDisplayMode()
    {
        if ($this->getParam(self::PARAM_IS_EXPORTED)) {
            $displayMode = $this->getParam(self::PARAM_DISPLAY_MODE);
        } elseif ('main' == \XLite\Core\Request::getInstance()->target) {
            $displayMode = $this->getRootCategoryDisplayMode();
        } else {
            $displayMode = \XLite\Core\Config::getInstance()->General->subcategories_look;
        }

        return $displayMode;
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && $this->isCategoryVisible() && $this->hasSubcategories();
    }

    /**
     * Widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_DISPLAY_MODE => new \XLite\Model\WidgetParam\Set(
                'Display mode', $this->getDisplayMode(), true, $this->displayModes
            ),
            self::PARAM_ICON_MAX_WIDTH => new \XLite\Model\WidgetParam\Int(
                'Maximal icon width', 160, true
            ),
            self::PARAM_ICON_MAX_HEIGHT => new \XLite\Model\WidgetParam\Int(
                'Maximal icon height', 160, true
            ),
        );
    }

    /**
     * Return the maximal icon width
     *
     * @return integer
     */
    protected function getIconWidth()
    {
        return $this->getParam(self::PARAM_ICON_MAX_WIDTH);
    }

    /**
     * Return the maximal icon height
     *
     * @return integer
     */
    protected function getIconHeight()
    {
        return $this->getParam(self::PARAM_ICON_MAX_HEIGHT);
    }

    /**
     * getColumnsCount
     *
     * @return integer
     */
    protected function getColumnsCount()
    {
        return 3;
    }

    /**
     * Return subcategories split into rows
     *
     * @return array
     */
    protected function getCategoryRows()
    {
        $rows = array_chunk($this->getSubcategories(), $this->getColumnsCount());
        $last = count($rows) - 1;
        $rows[$last] = array_pad($rows[$last], $this->getColumnsCount(), false);

        return $rows;
    }

    /**
     * Check for subcategories
     *
     * @return boolean
     */
    protected function hasSubcategories()
    {
        return $this->getCategory() ? $this->getCategory()->hasSubcategories() : false;
    }

    /**
     * Return subcategories
     *
     * @return array
     */
    protected function getSubcategories()
    {
        return $this->getCategory() ? $this->getCategory()->getSubcategories() : array();
    }

    /**
     * Check if the category is visible
     *
     * @return boolean
     */
    protected function isCategoryVisible()
    {
        return $this->getCategory() ? $this->getCategory()->isVisible() : false;
    }

    // {{{ Cache

    /**
     * Cache availability
     *
     * @return boolean
     */
    protected function isCacheAvailable()
    {
        return true;
    }

    /**
     * Get cache parameters
     *
     * @return array
     */
    protected function getCacheParameters()
    {
        $list = parent::getCacheParameters();

        $list[] = $this->getCategoryId();

        $auth = \XLite\Core\Auth::getInstance();
        $list[] = ($auth->isLogged() && $auth->getProfile()->getMembership())
            ? $auth->getProfile()->getMembership()->getMembershipId()
            : '-';

        return $list;
    }

    /**
     * Get display mode of the front page
     *
     * @return array
     */
    protected function getRootCategoryDisplayMode()
    {
        $displayMode = $this->getCategory()->getRootCategoryLook();

        if (!$displayMode) {
            $displayMode = self::DEFAULT_DISPLAY_MODE;
        }

        return $displayMode;
    }

    // }}}

}
