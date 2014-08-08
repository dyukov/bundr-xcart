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

namespace XLite\View\LanguagesModify;

/**
 * Language import process page
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class LanguageImport extends \XLite\View\SimpleDialog
{
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();
        $list[] = 'language_import';

        return $list;
    }


    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getBody()
    {
        return 'languages/import/body.tpl';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && (boolean)$this->getImportFilePath();
    }

    /**
     * Return title
     *
     * @return string
     */
    protected function getHead()
    {
        return 'Import language';
    }

    /**
     * Get import file path
     *
     * @return string
     */
    protected function getImportFilePath()
    {
        return \XLite\Core\Session::getInstance()->language_import_file;
    }

    /**
     * Return status of import file checking (true on success, false on failure)
     *
     * @return boolean
     */
    protected function isSuccess()
    {
        $importData = $this->parseImportFile();

        return 0 < $importData['codes'];
    }

    /**
     * Get error message
     *
     * @return string
     */
    protected function getMessage()
    {
        return null;
    }

    /**
     * Get array with summary information about import file data
     *
     * @return array
     */
    protected function getImportFileData()
    {
        $result = array();

        $importData = $this->parseImportFile();

        foreach ($importData['codes'] as $code => $lngData) {
            $result['codes'][] = array(
                'code'         => strtoupper($code),
                'language'     => $lngData['language'],
                'labels_count' => $lngData['count'],
            );
        }

        $result['ignored'] = $importData['ignored'];
        $result['elapsed'] = $importData['elapsed'];

        return $result;
    }

    /**
     * Get result of parsing import file
     *
     * @return array
     */
    protected function parseImportFile()
    {
        return \XLite\Core\Session::getInstance()->language_import_result
            ?: \XLite\Core\Database::getRepo('XLite\Model\Language')->parseImportFile($this->getImportFilePath());
    }

    /**
     * Return true if import has been finished and reset import state flags
     *
     * @return boolean
     */
    protected function isImportFinished()
    {
        return (boolean)\XLite\Core\Session::getInstance()->language_import_result;
    }
}
