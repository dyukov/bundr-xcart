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
 * Upgrade
 */
class Upgrade extends \XLite\Controller\Admin\Base\Addon
{
    // {{{ Common methods

    /**
     * Run controller
     *
     * @return void
     */
    protected function run()
    {
        // Clear marketplace server error flag
        \XLite\Core\Session::getInstance()->mpServerError = null;

        // Clear all selection if you visit the "Available updates" page
        if ($this->isUpdate()) {
            \XLite\Upgrade\Cell::getInstance()->clear();
        }

        if (\XLite\Upgrade\Cell::getInstance()->isUpgraded()) {

            if ($this->isForce()) {
                // Module is installed - redirect to the installed modules list
                $this->setReturnURL(
                    $this->buildURL(\XLite\Core\Request::getInstance()->redirect ?: 'addons_list_installed', '', array('recent' => true))
                );
                \XLite\Core\Marketplace::getInstance()->clearActionCache();

            } else {

                // Upgrade is completed

                $skipPostUpgradeAction = false;

                if (\XLite\Core\Session::getInstance()->flagIsUpgraded) {
                    \XLite\Upgrade\Cell::getInstance()->clear();
                    \XLite\Core\Session::getInstance()->flagIsUpgraded = null;
                    $skipPostUpgradeAction = true;
                } else {
                    \XLite\Core\Session::getInstance()->flagIsUpgraded = true;
                }

                if (!$skipPostUpgradeAction) {
                    // post_rebuild hooks running
                    \XLite\Upgrade\Cell::getInstance()->runHelpers('post_rebuild');
                    \XLite\Upgrade\Cell::getInstance()->runCommonHelpers('add_labels');

                    \XLite\Core\Marketplace::getInstance()->clearActionCache();
                    \XLite\Core\Session::getInstance()->flagIsUpgraded = null;
                }
            }
        } else {
            \XLite\Core\Session::getInstance()->flagIsUpgraded = null;
        }

        parent::run();
    }

    // }}}

    // {{{ Methods for viewers

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        if ($this->isCoreSelection()) {
            $result = 'Upgrade core';

        } elseif ($this->isDownload()) {
            $result = 'Downloading updates';

        } else {
            $version = \XLite\Upgrade\Cell::getInstance()->getCoreMajorVersion();

            if (\XLite::getInstance()->checkVersion($version, '<')) {
                $result = 'Upgrade to version {{version}}';

            } else {
                $result = 'Updates for your version ({{version}})';
            }

            $result = static::t($result, array('version' => $version));
        }

        return $result;
    }

    /**
     * Check if core major version is equal to the current one
     *
     * @return boolean
     */
    public function isUpdate()
    {
        return 'install_updates' === \XLite\Core\Request::getInstance()->mode;
    }

    /**
     * Check if current page is the core version selection dialog
     *
     * @return boolean
     */
    public function isCoreSelection()
    {
        return 'select_core_version' === \XLite\Core\Request::getInstance()->mode;
    }

    /**
     * Check if current page is the updates download dialog
     *
     * @return boolean
     */
    public function isDownload()
    {
        return 'download_updates' === \XLite\Core\Request::getInstance()->mode;
    }

    /**
     * Check if next step of upgrade id available
     *
     * @return boolean
     */
    public function isNextStepAvailable()
    {
        return \XLite\Upgrade\Cell::getInstance()->isValid()
            && (!\XLite\Upgrade\Cell::getInstance()->hasCoreUpdate() || \XLite::getXCNLicense());
    }

    /**
     * Check if the trial notice must be displayed
     *
     * @return boolean
     */
    public function displayTrialNotice()
    {
        return \XLite\Upgrade\Cell::getInstance()->hasCoreUpdate() && !\XLite::getXCNLicense();
    }

    /**
     * Return list of all core versions available
     *
     * @return array
     */
    public function getCoreVersionsList()
    {
        $result = \XLite\Upgrade\Cell::getInstance()->getCoreVersions();
        unset($result[\XLite::getInstance()->getMajorVersion()]);

        return $result;
    }

    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        return array_merge(parent::defineFreeFormIdActions(), array('view_log_file', 'view'));
    }

    /**
     * Check the flag in request
     *
     * @return boolean
     */
    protected function isForce()
    {
        return (bool) \XLite\Core\Request::getInstance()->force;
    }

    /**
     * Get some common params for actions
     *
     * @param boolean $force Flag OPTIONAL
     *
     * @return array
     */
    protected function getActionParamsCommon($force = null)
    {
        return ($force ?: $this->isForce()) ? array('force' => true) : array();
    }

    // }}}

    // {{{ Action handlers

    /**
     * Install add-ons from marketplace
     *
     * @return void
     */
    protected function doActionInstallAddon()
    {
        \XLite\Upgrade\Cell::getInstance()->clear(true, true, !$this->isForce());
        \XLite\Controller\Admin\AddonsListMarketplace::cleanModulesToInstall();

        foreach (\XLite\Core\Request::getInstance()->moduleIds as $moduleId) {
            $module = \XLite\Core\Database::getRepo('\XLite\Model\Module')->find($moduleId);
            if (!$module) {
                $this->showError(
                    __FUNCTION__,
                    static::t('invalid module ID passed: X', array('moduleId' => $moduleId))
                );

            } elseif (!$module->getFromMarketplace()) {
                $this->showError(
                    __FUNCTION__,
                    static::t('trying to install a non-marketplace module: X', array('name' => $module->getActualName()))
                );

            } elseif (!\XLite\Upgrade\Cell::getInstance()->addMarketplaceModule($module, true)) {
                $this->showError(
                    __FUNCTION__,
                    static::t('unable to add module entry to the installation list: X', array('path' => $module->getActualName()))
                );

            } else {
                \XLite\Controller\Admin\Base\AddonsList::storeRecentlyInstalledModules(array($module->getModuleID()));

                if (!$this->isNextStepAvailable()) {
                    $this->showError(__FUNCTION__);

                } elseif ($this->isForce()) {
                    $this->setHardRedirect(true);
                    $this->setReturnURL($this->buildURL('upgrade', 'download', $this->getActionParamsCommon()));
                }
            }
        }
    }

    /**
     * Install uploaded add-on
     *
     * @return void
     */
    protected function doActionUploadAddon()
    {
        $this->setReturnURL($this->buildURL('addons_list_installed'));

        $path = \Includes\Utils\FileManager::moveUploadedFile('modulePack');

        if ($path) {
            \XLite\Upgrade\Cell::getInstance()->clear(true, true, false);
            $entry = \XLite\Upgrade\Cell::getInstance()->addUploadedModule($path);

            if (!isset($entry)) {
                $this->showError(
                    __FUNCTION__,
                    static::t('unable to add module entry to the installation list: X', array('path' => $path))
                );

            } elseif (\XLite::getInstance()->checkVersion($entry->getMajorVersionNew(), '!=')) {
                $this->showError(
                    __FUNCTION__,
                    static::t(
                        'module version X is not equal to the core one (Y)',
                        array(
                            'module_version' => $entry->getMajorVersionNew(),
                            'core_version'   => \XLite::getInstance()->getMajorVersion(),
                        )
                    )
                );

            } elseif ($this->isNextStepAvailable()) {
                $this->setReturnURL($this->buildURL('upgrade', 'download', $this->getActionParamsCommon(true)));

            } else {
                $this->showError(__FUNCTION__);
            }

        } else {
            $this->showError(__FUNCTION__, static::t('unable to upload module'));
        }
    }

    /**
     * Go to the upgrade third step
     *
     * @return void
     */
    protected function doActionDownload()
    {
        $this->setReturnURL($this->buildURL('upgrade'));

        if ($this->isNextStepAvailable()) {
            \Includes\Utils\Operator::showMessage('Downloading updates, please wait...');

            // Disable some modules (if needed)
            \XLite\Upgrade\Cell::getInstance()->setIncompatibleModuleStatuses(
                (array) \XLite\Core\Request::getInstance()->toDisable
            );

            if ($this->runStep('downloadUpgradePacks')) {
                $this->setReturnURL($this->buildURL('upgrade', 'unpack', $this->getActionParamsCommon()));

            } else {
                $this->showError(__FUNCTION__, static::t('not all upgrade packs were downloaded'));
            }

        } else {
            $this->showWarning(__FUNCTION__, static::t('not ready to download packs. Please, try again'));
        }
    }

    /**
     * Go to the upgrade third step
     *
     * @return void
     */
    protected function doActionUnpack()
    {
        $this->setReturnURL($this->buildURL('upgrade'));

        if (\XLite\Upgrade\Cell::getInstance()->isDownloaded()) {
            \Includes\Utils\Operator::showMessage('Unpacking archives, please wait...');

            if (!$this->runStep('unpackAll')) {
                $this->showError(__FUNCTION__, static::t('not all archives were unpacked'));

                \XLite\Core\TopMessage::addError($this->getUnpackErrorMessage());

            } elseif ($this->isNextStepAvailable()) {
                $this->setReturnURL($this->buildURL('upgrade', 'check_integrity', $this->getActionParamsCommon()));

            } else {
                $this->showError(__FUNCTION__);
            }

        } else {
            $this->showError(__FUNCTION__, static::t('trying to unpack non-downloaded archives'));
        }
    }

    /**
     * Returns error message for 'unpack' error
     *
     * @return string
     */
    protected function getUnpackErrorMessage()
    {
        return 'Try to unpack them manually, and click <a href="'
            . $this->buildURL('upgrade', 'check_integrity')
            . '">this link</a>';
    }

    /**
     * Go to the upgrade third step
     *
     * @return void
     */
    protected function doActionCheckIntegrity()
    {
        $this->setReturnURL($this->buildURL('upgrade'));

        if (\XLite\Upgrade\Cell::getInstance()->isUnpacked()) {
            \Includes\Utils\Operator::showMessage('Checking integrity, please wait...');

            // Perform upgrade in test mode
            $this->runStep('upgrade', array(true));

            if ($this->isForce() && $this->isNextStepAvailable()) {
                $this->setReturnURL($this->buildURL('upgrade', 'install_upgrades', $this->getActionParamsCommon()));
            }

        } else {
            $this->showError(__FUNCTION__, static::t('unable to test files: not all archives were unpacked'));
        }
    }

    /**
     * Third step: install downloaded upgrades
     *
     * @return void
     */
    protected function doActionInstallUpgrades()
    {
        $this->setReturnURL($this->buildURL('upgrade'));

        if (\XLite\Upgrade\Cell::getInstance()->isUnpacked()) {
            \Includes\Utils\Operator::showMessage('Installing updates, please wait...');

            if (\XLite\Core\Request::getInstance()->preUpgradeWarningModules) {
                \XLite\Upgrade\Cell::getInstance()->setPreUpgradeWarningModules(
                    array_keys(
                        array_filter(
                            \XLite\Core\Request::getInstance()->preUpgradeWarningModules,
                            function($value) {
                                return 0 == $value;
                            }
                        )
                    )
                );
            }

            // Disable selected modules
            $modules = array();
            foreach (\XLite\Upgrade\Cell::getInstance()->getIncompatibleModules(true) as $module) {
                $module->setEnabled(false);
                $modules[] = $module;
            }

            \XLite\Core\Database::getRepo('\XLite\Model\Module')->updateInBatch($modules);

            // Do actions according the admin choice for the disabled modules with hooks
            $modulesToEnable = array();

            foreach (\XLite\Upgrade\Cell::getInstance()->getDisabledModulesHooks() as $marketplaceId => $module) {
                $action = \XLite\Core\Request::getInstance()->disabledModulesHooks[$marketplaceId];

                if (1 == $action) {
                    // Enable module
                    $module->setEnabled(true);
                    $modulesToEnable[] = $module;

                } elseif (0 == $action) {
                    // Uninstall module
                    \XLite\Upgrade\Cell::getInstance()->removeModuleEntry($module);
                    $this->uninstallModule($module);
                }
            }

            \XLite\Core\Database::getRepo('\XLite\Model\Module')->updateInBatch($modulesToEnable);

            if (\XLite\Upgrade\Cell::getInstance()->getEntries()) {
                // Perform upgrade
                // pre_upgrade / post_upgrade hooks will be proceeded here
                $this->runStep('upgrade', array(false, $this->getFilesToOverWrite()));

                if ($this->isForce()) {
                    if ($this->isNextStepAvailable()) {
                        $target = 'installed';
                        $this->showInfo(
                            null, 1 < count($modules) ? static::t('Modules have been successfully installed') : static::t('Module has been successfully installed')
                        );
                    } else {
                        $target = 'marketplace';
                        $this->showError(__FUNCTION__);
                    }

                    $this->setReturnURL(
                        $this->buildURL(
                            'upgrade', '', $this->getActionParamsCommon() + array('redirect' => 'addons_list_' . $target)
                        )
                    );
                }
            } else {
                // All modules for upgrade were set for uninstallation
                // There are no upgrade procedures to perform
                \XLite\Core\Marketplace::getInstance()->clearActionCache();

                $this->setReturnURL($this->buildURL('addons_list_installed'));
            }

            // Set cell status
            \XLite\Upgrade\Cell::getInstance()->clear(true, false, false);
            \XLite\Upgrade\Cell::getInstance()->setUpgraded(true);

            // Rebuild cache
            \XLite::setCleanUpCacheFlag(true);
        } else {
            $this->showWarning(
                __FUNCTION__,
                static::t('unable to install: not all archives were unpacked. Please, try again')
            );
        }
    }

    /**
     * Show log file content
     *
     * @return void
     */
    protected function doActionViewLogFile()
    {
        $path = \XLite\Upgrade\Logger::getInstance()->getLastLogFile();

        if ($path) {

            header('Content-Type: text/plain', true);

            \Includes\Utils\Operator::flush(
                \Includes\Utils\FileManager::read($path)
            );

            exit (0);

        } else {
            \XLite\Core\TopMessage::addWarning('Log files not found');
        }
    }

    /**
     * Install add-on from marketplace
     *
     * @return void
     */
    protected function doActionInstallAddonForce()
    {
        $data = array('moduleIds' => \XLite\Core\Request::getInstance()->moduleIds) + $this->getActionParamsCommon(true);
        $this->setReturnURL($this->buildURL('upgrade', 'install_addon', $data));
    }

    /**
     * Select core version for upgrade
     *
     * @return void
     */
    protected function doActionSelectCoreVersion()
    {
        $version = \XLite\Core\Request::getInstance()->version;

        if ($version) {
            \XLite\Upgrade\Cell::getInstance()->setCoreVersion($version);
            \XLite\Upgrade\Cell::getInstance()->clear(false);
            $this->setHardRedirect();

        } else {
            \XLite\Core\TopMessage::addError('Unexpected error: version value is not passed');
        }
    }

    /**
     * Run an upgrade step
     *
     * @param string $method Upgrade cell method to call
     * @param array  $params Call params OPTIONAL
     *
     * @return mixed
     */
    protected function runStep($method, array $params = array())
    {
        return \Includes\Utils\Operator::executeWithCustomMaxExecTime(
            \Includes\Utils\ConfigParser::getOptions(array('marketplace', 'upgrade_step_time_limit')),
            array(\XLite\Upgrade\Cell::getInstance(), $method),
            $params
        );
    }

    // }}}

    // {{{ Some auxiliary methods

    /**
     * Retrive list of files that must be overwritten by request for install upgrades
     *
     * @return array
     */
    protected function getFilesToOverWrite()
    {
        $allFilesPlain = array();

        foreach (\XLite\Upgrade\Cell::getInstance()->getCustomFiles() as $files) {
            $allFilesPlain = array_merge($allFilesPlain, $files);
        }

        return \Includes\Utils\ArrayManager::filterByKeys(
            $allFilesPlain,
            array_keys((array) \XLite\Core\Request::getInstance()->toRemain),
            true
        );
    }

    // }}}
}
