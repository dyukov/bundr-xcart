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

namespace XLite\Controller\Admin;

/**
 * Import controller
 */
class Import extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        $list = parent::defineFreeFormIdActions();
        $list[] = 'cancel';
        $list[] = 'proceed';

        return $list;
    }

    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    public function checkACL()
    {
        return parent::checkACL() || \XLite\Core\Auth::getInstance()->isPermissionAllowed('manage import');
    }

    /**
     * Get importer
     *
     * @return \XLite\Logic\Import\Importer
     */
    public function getImporter()
    {
        if (!isset($this->importer)) {
            $state = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getEventName());
            $this->importer = $state && isset($state['options']) ? new \XLite\Logic\Import\Importer($state['options']) : false;
        }

        return $this->importer;
    }

    /**
     * Get list of import options
     *
     * @return array
     */
    protected function getImportOptionsList()
    {
        return array('clearImportDir', 'ignoreFileChecking');
    }

    /**
     * Import action
     *
     * @return void
     */
    protected function doActionImport()
    {
        foreach ($this->getImportOptionsList() as $key) {
            \XLite\Core\Database::getRepo('XLite\Model\Config')->createOption(
                array(
                    'category' => 'Import',
                    'name'     => $key,
                    'value'    => isset(\XLite\Core\Request::getInstance()->options[$key])
                        ? \XLite\Core\Request::getInstance()->options[$key]
                        : false,
                )
            );
        }
        \XLite\Core\Config::updateInstance();

        $dirTo = LC_DIR_VAR . \XLite\Logic\Import\Importer::getImportDir();

        if (!\Includes\Utils\FileManager::isExists($dirTo)) {
            \Includes\Utils\FileManager::mkdirRecursive($dirTo);
        }

        $filesToImport = array();
        if (
            $_FILES
            && isset($_FILES['files'])
            && $_FILES['files']['name']
            && $_FILES['files']['name'][0]
            && \Includes\Utils\FileManager::isDirWriteable($dirTo)
        ) {
            $list = glob($dirTo . LC_DS . '*');
            if ($list) {
                foreach ($list as $path) {
                    if (is_file($path)) {
                        \Includes\Utils\FileManager::deleteFile($path);

                    }
                }
            }

            $files = $_FILES['files'];
            foreach ($files['name'] as $key => $name) {
                $path = null;
                if (
                    $name
                    && UPLOAD_ERR_OK === $files['error'][$key]
                ) {
                    $path = \Includes\Utils\FileManager::getUniquePath($dirTo, $name ?: $files['name'][$key]);

                    if (move_uploaded_file($files['tmp_name'][$key], $path)) {
                        if (
                            \XLite\Core\Archive::getInstance()->isArchive($path)
                            || 'csv' == substr(strrchr($path, '.'), 1)
                        ) {
                            $filesToImport[] = $path;

                        } else {
                            \XLite\Core\TopMessage::addError(
                                'The "{{file}}" is not CSV or archive',
                                array('file' => $name)
                            );
                            \Includes\Utils\FileManager::deleteFile($path);
                        }

                    } else {
                        $path = null;
                    }
                }

                if (!$path) {
                    \XLite\Core\TopMessage::addError(
                        'The "{{file}}" file was not uploaded',
                        array('file' => $name)
                    );
                }
            }
        }

        if ($filesToImport) {
            \XLite\Logic\Import\Importer::run(
                $this->assembleImportOptions()
                + array('files' => $filesToImport)
            );
        }
    }

    /**
     * Preprocessor for no-action run
     *
     * @return void
     */
    protected function doNoAction()
    {
        $event = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getEventName());

        if (
            $event
            && isset($event['options'])
            && isset($event['options']['step'])
            && 0 == $event['options']['step']
            && \XLite\Core\EventTask::STATE_FINISHED == $event['state']
            && !\XLite\Logic\Import\Importer::hasErrors()
            && !\XLite\Logic\Import\Importer::hasWarnings()
            && isset($event['options']['columnsMetaData'])
            && $event['options']['columnsMetaData']
        ) {
            $count = 0;
            foreach ($event['options']['columnsMetaData'] as $v) {
                if (isset($v['count'])) {
                    $count =+ $v['count'];
                }
            }
            if ($count) {
                \XLite\Logic\Import\Importer::run(
                    $this->assembleImportOptions($event['options']) + array('step' => 1)
                );
            }
        }
    }

    /**
     * Preprocessor for proceed action
     *
     * @return void
     */
    protected function doActionProceed()
    {
        $event = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getEventName());

        if (
            $event
            && isset($event['options'])
            && isset($event['options']['step'])
            && 0 == $event['options']['step']
            && \XLite\Core\EventTask::STATE_FINISHED == $event['state']
            && !\XLite\Logic\Import\Importer::hasErrors()
        ) {
            \XLite\Logic\Import\Importer::run(
                $this->assembleImportOptions($event['options']) + array('step' => 1, 'warningsAccepted' => true)
            );
        }
    }

    /**
     * Assemble import options
     *
     * @param array $options Options OPTIONAL
     *
     * @return array
     */
    protected function assembleImportOptions(array $options = array())
    {
        $result = array();

        foreach ($this->getImportOptionsList() as $key) {
            $result[$key] = isset($options[$key])
                ? $options[$key]
                : \XLite\Core\Config::getInstance()->Import->$key;
        }

        $result['delimiter'] = \XLite\Core\Config::getInstance()->Units->csv_delim;

        return $result;
    }

    /**
     * Cancel
     *
     * @return void
     */
    protected function doActionCancel()
    {
        if (\XLite\Logic\Import\Importer::hasErrors() || \XLite\Logic\Import\Importer::hasWarnings()) {
            \XLite\Logic\Import\Importer::userBreak();

        } else {
            \XLite\Logic\Import\Importer::cancel();
            \XLite\Core\TopMessage::addWarning('Import has been cancelled.');
        }
    }

    /**
     * Reset
     *
     * @return void
     */
    protected function doActionReset()
    {
        \XLite\Logic\Import\Importer::cancel();

        $this->setReturnURL($this->buildURL('import'));
    }

    /**
     * Return current step
     *
     * @return integer
     */
    public function getCurrentStep()
    {
        $state = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getEventName());

        return $state && isset($state['options']) && isset($state['options']['step'])
            ? $state['options']['step']
            : 0;
    }

    /**
     * Get event name
     *
     * @return string
     */
    protected function getEventName()
    {
        return \XLite\Logic\Import\Importer::getEventName();
    }
}
