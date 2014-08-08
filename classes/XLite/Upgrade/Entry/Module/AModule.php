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

namespace XLite\Upgrade\Entry\Module;

/**
 * AModule
 */
abstract class AModule extends \XLite\Upgrade\Entry\AEntry
{
    /**
     * Update database records
     *
     * @return array
     */
    abstract protected function updateDBRecords();

    /**
     * Perform upgrade
     *
     * @param boolean    $isTestMode       Flag OPTIONAL
     * @param array|null $filesToOverwrite List of custom files to overwrite OPTIONAL
     *
     * @return void
     */
    public function upgrade($isTestMode = true, $filesToOverwrite = null)
    {
        parent::upgrade($isTestMode, $filesToOverwrite);

        if (!$isTestMode) {
            list($author, $name) = explode('\\', $this->getActualName());

            if (!$this->isValid()) {
                \Includes\SafeMode::markModuleAsUnsafe($author, $name);
            }

            $this->updateDBRecords();
        }
    }

    /**
     * Return path where the upgrade helper scripts are placed
     *
     * @return string
     */
    protected function getUpgradeHelperPath()
    {
        list($author, $name) = explode('\\', $this->getActualName());

        return \Includes\Utils\FileManager::getRelativePath(
            \Includes\Utils\ModulesManager::getAbsoluteDir($author, $name),
            LC_DIR_ROOT
        ) . LC_DS;
    }

    /**
     * Get yaml file name to run common helper 'add_labels'
     *
     * @return string
     */
    protected function getCommonHelperAddLabelsFile()
    {
        list($author, $name) = explode('\\', $this->getActualName());

        $file = \Includes\Utils\ModulesManager::getAbsoluteDir($author, $name) . 'install.yaml';

        if (!\Includes\Utils\FileManager::isExists($file)) {
            $file = null;
        }

        return $file;
    }
}
