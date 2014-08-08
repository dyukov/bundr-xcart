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

namespace XLite\Logic\Import;

/**
 * Importer
 */
class Importer extends \XLite\Base
{
    /**
     * Default import directory
     */
    const IMPORT_DIR = 'import';

    /**
     * Language code
     *
     * @var string
     */
    static protected $languageCode;

    /**
     * Options
     *
     * @var   \ArrayObject
     */
    protected $options;

    /**
     * Steps (cache)
     *
     * @var   array
     */
    protected $steps;

    /**
     * Import processors list (cache)
     *
     * @var   array
     */
    protected $processors;

    /**
     * Constructor
     *
     * @param array $options Options OPTIONAL
     *
     * @return void
     */
    public function __construct(array $options = array())
    {
        $this->options = array(
            'step'         => isset($options['step']) ? intval($options['step']) : 0,
            'position'     => isset($options['position']) ? intval($options['position']) : 0,
            'delimiter'    => isset($options['delimiter']) ? $options['delimiter'] : ',',
            'enclosure'    => isset($options['enclosure']) ? $options['enclosure'] : '"',
            'files'        => isset($options['files']) ? $options['files'] : array(),
            'linkedFiles'  => isset($options['linkedFiles']) ? $options['linkedFiles'] : array(),
            'clearImportDir' => isset($options['clearImportDir']) ? $options['clearImportDir'] : false,
            'ignoreFileChecking' => isset($options['ignoreFileChecking']) ? $options['ignoreFileChecking'] : false,
            'dir'          => isset($options['dir']) ? $options['dir'] : LC_DIR_VAR . static::getImportDir(),
            'time'         => isset($options['time']) ? intval($options['time']) : 0,
            'columnsMetaData' => isset($options['columnsMetaData']) ? $options['columnsMetaData'] : array(),
            'errorsCount'  => isset($options['errorsCount']) ? $options['errorsCount'] : 0,
            'warningsCount' => isset($options['warningsCount']) ? $options['warningsCount'] : 0,
            'rowsCount'    => isset($options['rowsCount']) ? $options['rowsCount'] : 0,
        ) + $options;

        static::$languageCode = isset($options['languageCode'])
            ? $options['languageCode']
            : \XLite\Core\Config::getInstance()->General->default_admin_language;

        $this->options = new \ArrayObject($this->options, \ArrayObject::ARRAY_AS_PROPS);

        if (0 == $this->getOptions()->step && 0 == $this->getOptions()->position) {
            $this->initialize();
        }
    }

    /**
     * Get language code
     *
     * @return string
     */
    static public function getLanguageCode() {
        return static::$languageCode;
    }

   /**
     * Run
     *
     * @param array $options Options
     *
     * @return void
     */
    public static function run(array $options)
    {
        \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->setVar(static::getImportCancelFlagVarName(), false);
        \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->setVar(static::getImportUserBreakFlagVarName(), false);
        \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->initializeEventState(
            static::getEventName(),
            array('options' => $options)
        );
        \XLite\Core\EventTask::import();
        call_user_func(array('\XLite\Core\EventTask', static::getEventName()));
    }

    /**
     * Cancel import routine
     *
     * @return void
     */
    public static function cancel()
    {
        \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->setVar(static::getImportCancelFlagVarName(), true);
        \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->setVar(static::getImportUserBreakFlagVarName(), false);
        \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->removeEventState(static::getEventName());
    }

    /**
     * Break import routine
     *
     * @return void
     */
    public static function userBreak()
    {
        \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->setVar(static::getImportUserBreakFlagVarName(), true);
    }

    /**
     * Initialize
     *
     * @return void
     */
    protected function initialize()
    {
        // Unpack
        foreach ($this->getOptions()->files as $path) {
            if (\XLite\Core\Archive::getInstance()->isArchive($path)) {
                \XLite\Core\Archive::getInstance()->unpack($path, $this->getOptions()->dir, true);
                $this->getOptions()->linkedFiles[$path] = \XLite\Core\Archive::getInstance()->getList($path);
            }
        }

        // Delete all logs
        \XLite\Core\Database::getRepo('XLite\Model\ImportLog')->clearAll();
    }

    /**
     * Get options
     *
     * @return \ArrayObject
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Check importer state
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->getStep() && $this->getStep()->isValid() && !$this->hasErrors();
    }

    /**
     * Check importer state
     *
     * @return boolean
     */
    public function isImportAllowed()
    {
        return $this->valid() && (!$this->hasWarnings() || $this->getOptions()->warningsAccepted);
    }

    // {{{ Steps

    /**
     * Get step
     *
     * @return \XLite\Logic\Import\Step\AStep
     */
    public function getStep()
    {
        $steps = $this->getSteps();

        return isset($steps[$this->getOptions()->step]) ? $steps[$this->getOptions()->step] : null;
    }

    /**
     * Get steps
     *
     * @return array
     */
    public function getSteps()
    {
        if (!isset($this->steps)) {
            $this->steps = $this->defineSteps();
            $this->processSteps();
        }

        return $this->steps;
    }

    /**
     * Define steps
     *
     * @return array
     */
    protected function defineSteps()
    {
        return array(
            'XLite\Logic\Import\Step\Verification',
            'XLite\Logic\Import\Step\Import',
        );
    }

    /**
     * Process steps
     *
     * @return void
     */
    protected function processSteps()
    {
        foreach ($this->steps as $i => $stepClass) {
            $this->steps[$i] = new $stepClass($this);
        }
    }

    // }}}

    // {{{ Processors

    /**
     * Get processors
     *
     * @return array
     */
    public function getProcessors()
    {
        if (!isset($this->processors)) {
            $this->processors = $this->getProcessorList();
            $this->prepareProcessors();
        }

        return $this->processors;
    }

    /**
     * Get processor list
     *
     * @return array
     */
    static public function getProcessorList()
    {
        return array(
            'XLite\Logic\Import\Processor\Attributes',
            'XLite\Logic\Import\Processor\Categories',
            'XLite\Logic\Import\Processor\Products',
            'XLite\Logic\Import\Processor\Customers',
            'XLite\Logic\Import\Processor\AttributeValues\AttributeValueCheckbox',
            'XLite\Logic\Import\Processor\AttributeValues\AttributeValueSelect',
            'XLite\Logic\Import\Processor\AttributeValues\AttributeValueText',
        );
    }

    /**
     * Prepare processors
     *
     * @return void
     */
    protected function prepareProcessors()
    {
        foreach ($this->processors as $i => $processor) {
            if (\XLite\Core\Operator::isClassExists($processor)) {
                $this->processors[$i] = new $processor($this);

            } else {
                unset($this->processors[$i]);
            }
        }

        $this->processors = array_values($this->processors);
    }

    // }}}

    // {{{ Error / warning routines

    /**
     * Check - import process has warnings or not
     *
     * @return boolean
     */
    public static function hasWarnings()
    {
        return 0 < \XLite\Core\Database::getRepo('XLite\Model\ImportLog')
            ->countBy(array('type' => \XLite\Model\ImportLog::TYPE_WARNING));
    }

    /**
     * Check - import process has errors or not
     *
     * @return boolean
     */
    public static function hasErrors()
    {
        return 0 < \XLite\Core\Database::getRepo('XLite\Model\ImportLog')
            ->countBy(array('type' => \XLite\Model\ImportLog::TYPE_ERROR));
    }

    // }}}

    // {{{ Filesystem

    /**
     * Get CSV files list
     *
     * @return \Includes\Utils\FileFilter\FilterIterator
     */
    public function getCSVList()
    {
        if (!isset($this->csvFilter)) {
            $this->csvFilter = new \Includes\Utils\FileFilter($this->getOptions()->dir, '/\.csv$/Ss');
        }

        return $this->csvFilter->getIterator();
    }

    /**
     * Delete all files
     *
     * @return void
     */
    public function deleteAllFiles()
    {
        if (!\Includes\Utils\FileManager::isExists($this->getOptions()->dir)) {
            \Includes\Utils\FileManager::mkdir($this->getOptions()->dir);
        }

        $list = glob($this->getOptions()->dir . LC_DS . '*');
        if ($list) {
            foreach ($list as $path) {
                if (is_file($path)) {
                    \Includes\Utils\FileManager::deleteFile($path);

                } else {
                    \Includes\Utils\FileManager::unlinkRecursive($path);
                }
            }
        }
    }

    /**
     * Get import directory path
     *
     * @return string
     */
    public static function getImportDir()
    {
        return static::IMPORT_DIR;
    }

    // }}}

    // {{{ Service variable names

    /**
     * Get import cancel flag name
     *
     * @return string
     */
    public static function getImportCancelFlagVarName()
    {
        return 'importCancelFlag';
    }

    /**
     * Get import user break flag name
     *
     * @return string
     */
    public static function getImportUserBreakFlagVarName()
    {
        return 'importUserBreak';
    }

    /**
     * Get import event name
     *
     * @return string
     */
    public static function getEventName()
    {
        return 'import';
    }

    // }}}
}
