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

namespace XLite\Controller\Admin\Base;

/**
 * Addon
 */
abstract class Addon extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Uninstall module action
     *
     * @param \XLite\Model\Module $module Module object to uninstall
     *
     * @return boolean
     */
    protected function uninstallModule(\XLite\Model\Module $module)
    {
        $result = false;

        $pack = new \XLite\Core\Pack\Module($module);
        $dirs = $pack->getDirs();

        $nonWritableDirs = array();

        // Check permissions
        foreach ($dirs as $dir) {
            if (
                \Includes\Utils\FileManager::isExists($dir)
                && !\Includes\Utils\FileManager::isDirWriteable($dir)
            ) {
                $nonWritableDirs[] = \Includes\Utils\FileManager::getRelativePath($dir, LC_DIR_ROOT);
            }
        }

        $params = array(
            'name' => sprintf('%s (%s)', $module->getModuleName(), $module->getAuthorName()),
        );

        if (empty($nonWritableDirs)) {
            $yaml = \Includes\Utils\FileManager::read(
                \Includes\Utils\ModulesManager::getModuleYAMLFile($module->getAuthor(), $module->getName())
            );

            if (!$module->checkModuleMainClass()) {
                $classFile = LC_DIR_CLASSES . \Includes\Utils\Converter::getClassFile($module->getMainClass());

                if (\Includes\Utils\FileManager::isFileReadable($classFile)) {
                    require_once $classFile;
                }
            }

            // Call uninstall event method
            $r = $module->callModuleMethod('callUninstallEvent', 111);
            if (111 == $r) {
                \XLite\Logger::getInstance()->log($module->getActualName() . ': Method callUninstallEvent() was not called');
            }

            // Remove from FS
            foreach ($dirs as $dir) {
                \Includes\Utils\FileManager::unlinkRecursive($dir);
            }

            \Includes\Utils\ModulesManager::disableModule($module->getActualName());
            \Includes\Utils\ModulesManager::removeModuleFromDisabledStructure($module->getActualName());

            // Remove from DB
            \XLite\Core\Database::getRepo('XLite\Model\Module')->delete($module);

            if ($module->getModuleID()) {
                $this->showError(
                    __FUNCTION__, static::t('A DB error occured while uninstalling the module X', $params)
                );
            } else {
                if (!empty($yaml)) {
                    \XLite\Core\Database::getInstance()->unloadFixturesFromYaml($yaml);
                }

                $result = true;
                $this->showInfo(
                    __FUNCTION__, static::t('The module X has been uninstalled successfully', $params)
                );
            }
        } else {
            $this->showError(
                __FUNCTION__,
                static::t(
                    'Unable to delete module X files: some dirs have no writable permissions: Y',
                    $params + array(
                        'dirs' => implode(', ', $nonWritableDirs),
                    )
                )
            );
        }

        return $result;
    }
}
