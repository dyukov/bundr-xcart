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

namespace XLite\Controller\Admin;

/**
 * AddonsListInstalled
 */
class AddonsListInstalled extends \XLite\Controller\Admin\Base\AddonsList
{
    /**
     * Define and set handler attributes; initialize handler
     *
     * @param array $params Handler params OPTIONAL
     *
     * @return void
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);

        if (\XLite\Core\Session::getInstance()->returnURL) {
            $this->setReturnURL(\XLite\Core\Session::getInstance()->returnURL);
            \XLite\Core\Session::getInstance()->returnURL = '';

            $this->redirect();
        }
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->isRecentlyInstalledPage()
            ? 'Recently installed modules'
            : 'Installed Modules';
    }

    /**
     * Substring search getter
     *
     * @return string
     */
    public function getSubstring()
    {
        return \XLite\Core\Request::getInstance()->substring;
    }

    /**
     * The recently installed page flag
     *
     * @return boolean
     */
    public function isRecentlyInstalledPage()
    {
        return isset(\XLite\Core\Request::getInstance()->recent) && (count(static::getRecentlyInstalledModuleList()) > 0);
    }

    // {{{ Short-name methods

    /**
     * Return module identificator
     *
     * @return integer
     */
    protected function getModuleId()
    {
        return \XLite\Core\Request::getInstance()->moduleId;
    }

    /**
     * Search for module
     *
     * @return \XLite\Model\Module|void
     */
    protected function getModule()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Module')->find($this->getModuleId());
    }

    /**
     * Search for modules
     *
     * @param string $cellName Request cell name
     *
     * @return array
     */
    protected function getModules($cellName)
    {
        $modules = array();

        foreach (((array) \XLite\Core\Request::getInstance()->$cellName) as $id => $value) {
            $modules[] = \XLite\Core\Database::getRepo('\XLite\Model\Module')->find(intval($id));
        }

        return array_filter($modules);
    }

    // }}}

    // Action handlers

    /**
     * Enable module
     *
     * :TODO: TO REMOVE?
     *
     * @return void
     */
    protected function doActionEnable()
    {
        $module = $this->getModule();

        if ($module) {

            // Update data in DB
            // :TODO: this action should be performed via ModulesManager
            $module->setEnabled(true);
            $module->getRepository()->update($module);

            // Flag to rebuild cache
            \XLite::setCleanUpCacheFlag(true);
        }
    }

    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        return array_merge(parent::defineFreeFormIdActions(), array('pack'));
    }

    /**
     * Pack module into PHAR module file
     *
     * @return void
     */
    protected function doActionPack()
    {
        if (LC_DEVELOPER_MODE) {
            $module = $this->getModule();

            if ($module) {
                if ($module->getEnabled()) {
                    \Includes\Utils\PHARManager::packModule(new \XLite\Core\Pack\Module($module));

                } else {
                    \XLite\Core\TopMessage::addError('Only enabled modules can be packed');
                }

            } else {
                \XLite\Core\TopMessage::addError('Module with ID X is not found', array('id' => $this->getModuleId()));
            }

        } else {
            \XLite\Core\TopMessage::addError(
                'Module packing is available in the DEVELOPER mode only. Check etc/config.php file'
            );
        }
    }

    /**
     * Uninstall module
     *
     * @return void
     */
    protected function doActionUninstall()
    {
        $module = $this->getModule();
        if ($module) {
            if (!defined('LC_MODULE_CONTROL')) {
                define('LC_MODULE_CONTROL', true);
            }

            $result = $this->uninstallModule($module);

            if ($result) {
                // To restore previous state
                \XLite\Core\Marketplace::getInstance()->saveAddonsList(0);
                // Flag to rebuild cache
                \XLite::setCleanUpCacheFlag(true);
            }
        }
    }

    /**
     * Switch module
     *
     * @return void
     */
    protected function doActionSwitch()
    {
        $changed = false;
        $data    = (array) \XLite\Core\Request::getInstance()->switch;
        $modules = array();
        $firstModule = null;

        foreach ($this->getModules('switch') as $module) {
            $old = !empty($data[$module->getModuleId()]['old']);
            $new = !empty($data[$module->getModuleId()]['new']);

            if ($old !== $new) {
                $module->setEnabled($new);

                // Call disable event to make some module specific changes
                if ($old) {
                    $module->callModuleMethod('callDisableEvent');
                } elseif (is_null($firstModule)) {
                    $firstModule = $module;
                }

                $modules[] = $module;
                $changed = true;
            }
        }

        // Flag to rebuild cache
        if ($changed) {
            // We redirect the admin to the modules page on the activated module anchor
            // The first module in a batch which is available now
            \XLite\Core\Session::getInstance()->returnURL =
                $firstModule ? $this->getModulePageURL($firstModule) : (\XLite\Core\Request::getInstance()->return ?: '');

            \XLite\Core\Database::getRepo('\XLite\Model\Module')->updateInBatch($modules);
            \XLite::setCleanUpCacheFlag(true);
        }
    }

    /**
     * Module page URL getter
     *
     * @param \XLite\Model\Module $module Module model
     *
     * @return string
     */
    protected function getModulePageURL(\XLite\Model\Module $module)
    {
        $params = array(
            'clearCnd' => 1,
            'pageId' => \XLite\Core\Database::getRepo('XLite\Model\Module')->getModulePageId(
                $module->getAuthor(),
                $module->getName(),
                \XLite\View\Pager\Admin\Module\Manage::getInstance()->getItemsPerPage()
            ),
        );

        return $this->buildURL('addons_list_installed', '', $params) . '#' . $module->getName();
    }

    /**
     * Perform some actions before redirect
     *
     * @param string $action Performed action
     *
     * @return void
     */
    protected function actionPostprocess($action)
    {
        parent::actionPostprocess($action);

        $this->setReturnURL($this->buildURL('addons_list_installed'));
    }

    // }}}
}
