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

namespace XLite\View\ItemsList\Module;

/**
 * Addons search and installation widget
 */
class Manage extends \XLite\View\ItemsList\Module\AModule
{
    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();
        $result[] = 'addons_list_installed';

        return $result;
    }

    /**
     * Register JS files. TODO REWORK with Popup button widget
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = $this->getDir() . '/' . $this->getPageBodyDir() . '/js/controller.js';

        return $list;
    }

    /**
     * Return name of the base widgets list
     *
     * @return string
     */
    protected function getListName()
    {
        return parent::getListName() . '.manage';
    }

    /**
     * Return dir which contains the page body template
     *
     * @return string
     */
    protected function getPageBodyDir()
    {
        return 'manage';
    }

    /**
     * Return class name for the list pager
     *
     * @return string
     */
    protected function getPagerClass()
    {
        return '\XLite\View\Pager\Admin\Module\Manage';
    }

    /**
     * Substring search getter
     *
     * @return string
     */
    protected function getSubstring()
    {
        return parent::getSubstring() ?: $this->getParam(static::PARAM_SUBSTRING);
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $result = parent::getSearchCondition();
        if ($this->isRecentlyInstalledPage()) {
            $result->{\XLite\Model\Repo\Module::P_MODULEIDS} = \XLite\Controller\Admin\Base\AddonsList::getRecentlyInstalledModuleList();
        } else {
            $result->{\XLite\Model\Repo\Module::P_INSTALLED} = true;
            $result->{\XLite\Model\Repo\Module::P_ISSYSTEM}  = false;
            if (!isset(\XLite\Core\Request::getInstance()->clearCnd)) {
                $result->{\XLite\Model\Repo\Module::P_SUBSTRING} = $this->getSubstring();
            }
        }

        return $result;
    }

    /**
     * Auxiliary method to check visibility
     *
     * @return boolean
     */
    protected function isDisplayWithEmptyList()
    {
        return true;
    }

    /**
     * Get URL common parameters
     *
     * @return array
     */
    protected function getCommonParams()
    {
        $list = parent::getCommonParams();
        if (!isset($this->commonParams['substring'])) {
            $this->commonParams['substring'] = $this->getSubstring();
        }
        return $this->commonParams;
    }


    /**
     * Return filters array
     *
     * @return array
     */
    protected function getFilters()
    {
        return array(
            ''                                   => 'All',
            \XLite\Model\Repo\Module::P_INACTIVE => 'Inactive',
        );
    }

    /**
     * Get current filter
     *
     * @return string
     */
    protected function getFilter()
    {
        $filter = \XLite\Core\Request::getInstance()->filter;

        if (empty($filter) || !in_array($filter, array_keys($this->getFilters()))) {
            $filter = '';
        }

        return $filter;
    }

    /**
     *  Get classes names for filter item
     *
     * @param string $filter Name of filter
     *
     * @return string
     */
    protected function getFilterClasses($filter)
    {
        return $filter === $this->getFilter() ? 'current' : '';
    }

    /**
     * Return number of modules with certain type
     *
     * @param string $filter Filter criterion
     *
     * @return integer
     */
    protected function getModulesCount($filter)
    {
        return $this->getData($this->getSearchCondition(), true, $filter);
    }

    /**
     * Return modules list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     * @param string                 $filter    Filter criterion OPTIONAL
     *
     * @return array|integer
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false, $filter = null)
    {
        if (!isset($filter)) {
            $filter = $this->getFilter();
        }

        if (!empty($filter)) {
            $cnd->$filter = true;
        }

        return parent::getData($cnd, $countOnly);
    }

    // {{{ Methods to search modules of certain types

    /**
     * Check if core requires new (but the same as core major) version of module
     *
     * @param \XLite\Model\Module $module Module to check
     *
     * @return boolean
     */
    protected function isModuleUpdateAvailable(\XLite\Model\Module $module)
    {
        return $module->isInstalled()
            && $this->isModuleCompatible($module)
            && $this->getModuleForUpdate($module);
    }

    /**
     * Check if core requires new (but the same as core major) version of module
     *
     * @param \XLite\Model\Module $module Module to check
     *
     * @return boolean
     */
    protected function isModuleUpgradeAvailable(\XLite\Model\Module $module)
    {
        return (bool)$this->getModuleForUpgrade($module);
    }

    /**
     * Check if the module is installed
     *
     * @param \XLite\Model\Module $module Module
     *
     * @return boolean
     */
    protected function isInstalled(\XLite\Model\Module $module)
    {
        return true;
    }

    /**
     * Check if there are some errors for the current module
     *
     * @param \XLite\Model\Module $module Module to check
     *
     * @return boolean
     */
    protected function hasErrors(\XLite\Model\Module $module)
    {
        return !$this->isEnabled($module)
            && parent::hasErrors($module);
    }

    // }}}

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_SUBSTRING => new \XLite\Model\WidgetParam\String(
                'Substring', ''
            ),
        );
    }

    /**
     * Check if the XC module notice must be displayed.
     * The notice is displayed when the module is a part of X-Cart 5 license
     * and current X-Cart 5 license type of core differs from X-Cart 5 license type of module.
     *
     * @param \XLite\Model\Module $module Module entity
     *
     * @return boolean
     */
    protected function showXCNModuleNotice(\XLite\Model\Module $module)
    {
        $marketplaceModule = \XLite\Core\Database::getRepo('XLite\Model\Module')
            ->findOneBy(array(
                'name'      => $module->getName(),
                'author'    => $module->getAuthor(),
                'fromMarketplace' => true
            ));

        return $marketplaceModule && (bool)\XLite::getXCNLicense() && $this->isXCN($marketplaceModule) && !$module->getEnabled() && 1 < $marketplaceModule->getEditionState();
    }

    /**
     * Check if the module can be enabled
     *
     * @param \XLite\Model\Module $module Module
     *
     * @return boolean
     */
    protected function canEnable(\XLite\Model\Module $module)
    {
        return parent::canEnable($module) && !$this->showXCNModuleNotice($module);
    }
}
