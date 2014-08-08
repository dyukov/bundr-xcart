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

namespace XLite\View\Export;

/**
 * Download files box
 */
class Download extends \XLite\View\AView
{

    /**
     * Widget parameters
     */
    const PARAM_COMPLETED_CONTEXT = 'completedContext';

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'export/download.tpl';
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
            static::PARAM_COMPLETED_CONTEXT => new \XLite\Model\WidgetParam\Bool('Complete section context', false),
        );
    }

    /**
     * Check - widget run into completed section context or not
     *
     * @return boolean
     */
    protected function isCompletedSection()
    {
        return $this->getParam(static::PARAM_COMPLETED_CONTEXT);
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && $this->getDownloadFiles();
    }

    /**
     * Check if bracket is visible
     *
     * @return boolean
     */
    protected function isBracketVisible()
    {
        return 1 < count($this->getDownloadFiles());
    }

    /**
     * Get box title
     *
     * @return string
     */
    protected function getBoxTitle()
    {
        return $this->isCompletedSection()
            ? static::t('Download CSV files')
            : static::t('Exported in X', array('date' => $this->getLastExportDate()));
    }

    /**
     * Get last export date
     *
     * @return string
     */
    protected function getLastExportDate()
    {
        $list = $this->getDownloadFiles();
        $file = $list ? current($list) : null;

        return $this->formatDate($file ? $file->getMTime() : \XLite\Core\Converter::time());

    }

    /**
     * Get download files
     *
     * @return array
     */
    protected function getDownloadFiles()
    {
        $result = array();

        if ($this->getGenerator()) {
            $len = strlen($this->getGenerator()->getOptions()->dir) + 1;
            foreach ($this->getGenerator()->getDownloadableFiles() as $path) {
                if (preg_match('/\.csv$/Ss', $path)) {
                    $key = substr($path, $len);
                    $result[$key] = new \SplFileInfo($path);
                }
            }
        }

        return $result;
    }

    /**
     * Get download large files
     *
     * @return array
     */
    protected function getDownloadLargeFiles()
    {
        $result = array();

        $len = strlen($this->getGenerator()->getOptions()->dir) + 1;
        foreach ($this->getGenerator()->getDownloadableFiles() as $path) {
            if (filesize($path) >= \XLite\Logic\Export\Generator::MAX_FILE_SIZE) {
                $key = substr($path, $len);
                $result[$key] = new \SplFileInfo($path);
            }
        }

        return $result;
    }

    /**
     * Get packed size
     *
     * @return integer
     */
    protected function getPackedSize()
    {
        $size = 0;

        foreach ($this->getGenerator()->getPackedFiles() as $path) {
            if (filesize($path) < \XLite\Logic\Export\Generator::MAX_FILE_SIZE) {
                $size += filesize($path);
            }
        }

        return $size;
    }

    /**
     * Get allowed pack types
     *
     * @return array
     */
    protected function getAllowedPackTypes()
    {
        return $this->getGenerator()->getAllowedArchives();
    }
}

