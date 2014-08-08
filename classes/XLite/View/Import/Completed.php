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

namespace XLite\View\Import;

/**
 * Completed section
 */
class Completed extends \XLite\View\AView
{
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'import/completed.tpl';
    }

    /**
     * Return massages
     *
     * @return array
     */
    protected function getMessages()
    {
        $result = array();
        $state = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getEventName());
        $data = $state['options']['columnsMetaData'];

        if ($data) {
            foreach (\XLite\Logic\Import\Importer::getProcessorList() as $processor) {
                if (isset($data[$processor])) {
                    $cur = $data[$processor];
                    $text = $processor::getTitle() . ': ';
                    $count = 0;
                    $comment = array();

                    if (
                        isset($cur['addCount'])
                        && $cur['addCount']
                    ) {
                        $count += $cur['addCount'];
                        $comment[] = static::t('{{count}} created', array('count' => $cur['addCount']));
                    }

                    if (
                        isset($cur['updateCount'])
                        && $cur['updateCount']
                    ) {
                        $count += $cur['updateCount'];
                        $comment[] = static::t('{{count}} updated', array('count' => $cur['updateCount']));
                    }

                    if (
                        isset($cur['count'])
                        && $cur['count']
                    ) {
                        $count += $cur['count'];
                    }

                    if ($count) {
                        $result[] = array(
                            'text'    => $text . $count,
                            'comment' => $comment ? '(' . implode(', ', $comment) . ')' : '',
                        );
                    }

                }
            }
        }

        return $result;
    }

    /**
     * Get error messages
     *
     * @return array
     */
    protected function getErrorMessages()
    {
        $result = array();

        if (!$this->hasCorrectFileNameFormat()) {
            // There are no valid CSV files found
            $result[] = array(
                'text'    => static::t('CSV file has the wrong filename format.'),
                'comment' => static::t('Possible import file names are:', array('files' => $this->getFileFormatsList())),
            );

        } else {
            // There are no data found in uploaded CSV files
            $result[] = array(
                'text'    => static::t('X-Cart could not find data in your file.'),
                'comment' => static::t(
                    'Possible reasons of data not found in import file',
                    array(
                        'separator' => \XLite\Core\Config::getInstance()->Units->csv_delim,
                        'encoding'  => \XLite\Core\Config::getInstance()->Units->export_import_charset,
                        'configURL' => $this->buildURL('units_formats'),
                        'kbURL'     => 'http://kb.x-cart.com/display/XDD/Import-Export',
                    )
                ),
            );
        }

        return $result;
    }

    /**
     * Get list of allowed import file formats
     *
     * @return string
     */
    protected function getFileFormatsList()
    {
        $result = array();

        $processors = \XLite\Logic\Import\Importer::getInstance()->getProcessors();

        foreach ($processors as $p) {
            $result[] = $p->getFileNameFormat();
        }

        $result = array_unique($result);

        return implode(', ', $result);
    }

    /**
     * Return true if CSV files have been processed by one of processors
     *
     * @return boolean
     */
    protected function hasCorrectFileNameFormat()
    {
        $result = false;

        $processors = \XLite\Logic\Import\Importer::getInstance()->getProcessors();

        foreach ($processors as $p) {
            if ($p->getFiles()) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * Get import event name
     *
     * @return string
     */
    protected function getEventName()
    {
        return \XLite\Logic\Import\Importer::getEventName();
    }
}
