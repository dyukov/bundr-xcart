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

namespace Includes\Decorator\Plugin\Doctrine\Plugin\UpdateModules;

/**
 * Main 
 *
 */
class Main extends \Includes\Decorator\Plugin\Doctrine\Plugin\APlugin
{
    /**
     * Execute certain hook handler
     *
     * @return void
     */
    public function executeHookHandler()
    {
        // To cache data
        \Includes\Utils\ModulesManager::getActiveModules();

        // Prepare flag to use it later for loading or not loading modules' yaml files
        $isModulesFileExists = \Includes\Utils\ModulesManager::isModulesFileExists();

        // Walk through the "XLite/Module" directory
        foreach ($this->getModuleMainFileIterator()->getIterator() as $path => $data) {
            $dir    = $path;
            $name   = basename($dir = dirname($dir));
            $author = basename($dir = dirname($dir));
            $class  = \Includes\Utils\ModulesManager::getClassNameByAuthorAndName($author, $name);

            if (!\Includes\Utils\Operator::checkIfClassExists($class)) {
                require_once ($path);
            }

            \Includes\Utils\ModulesManager::switchModule($author, $name, $isModulesFileExists);
        }

        \Includes\Utils\ModulesManager::removeFile();
    }

    /**
     * Get iterator for module files
     *
     * @return \Includes\Utils\FileFilter
     */
    protected function getModuleMainFileIterator()
    {
        return new \Includes\Utils\FileFilter(LC_DIR_MODULES, $this->getModulesPathPattern());
    }

    /**
     * Pattern to use for paths in "Module" directory
     *
     * @return string
     */
    protected function getModulesPathPattern()
    {
        return '|^' . preg_quote(LC_DIR_MODULES) . '(\w)+' . LC_DS_QUOTED . '(\w)+' . LC_DS_QUOTED . 'Main.php$|Si';
    }
}
