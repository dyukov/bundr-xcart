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

namespace XLite\Logic\Import\Step;

/**
 * Abstract import step
 */
abstract class AStep extends \XLite\Base implements \SeekableIterator, \Countable
{
    /**
     * Default import process tick duration
     */
    const DEFAUL_TICK_DURATION = 0.5;

    /**
     * Importer (cache)
     *
     * @var   \XLite\Logic\Import\Importer
     */
    protected $importer;

    /**
     * Last position (cache)
     *
     * @var   integer
     */
    protected $lastPosition = 0;

    /**
     * Last import processor (cache)
     *
     * @var   \XLite\Logic\Import\Processor\AProcessor
     */
    protected $lastProcessor;

    /**
     * Process row
     *
     * @return boolean
     */
    abstract public function process();

    /**
     * Constructor
     *
     * @param \XLite\Logic\Import\Importer $importer Importer
     *
     * @return void
     */
    public function __construct(\XLite\Logic\Import\Importer $importer)
    {
        $this->importer = $importer;

        $this->importer->getOptions()->rowsCount = $this->count();
    }

    /**
     * Get current processor
     *
     * @return \XLite\Logic\Import\Processor\AProcessor
     */
    protected function getProcessor()
    {
        if ($this->getOptions()->position != $this->lastPosition || !isset($this->lastProcessor)) {
            $i = $this->getOptions()->position;
            foreach ($this->importer->getProcessors() as $processor) {
                $this->lastProcessor = $processor;
                $count = $processor->count();

                if (0 >= $count) {
                    continue;
                }

                if ($i < $count) {
                    $processor->seek(max($i, 0));

                    if (!$processor->isEof()) {
                        break;
                    }
                }

                $i -= $count;
            }

            $this->lastPosition = $this->getOptions()->position;
        }

        return $this->lastProcessor;
    }

    /**
     * Check valid state of step
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->getProcessor() && $this->getProcessor()->isValid();
    }

    /**
     * Get time remain
     *
     * @return integer
     */
    public function getTimeRemain()
    {
        return $this->getTickDuration() * ($this->count() - $this->getOptions()->position);
    }

    /**
     * Get options
     *
     * @return \ArrayObject
     */
    public function getOptions()
    {
        return $this->importer->getOptions();
    }

    /**
     * Get import process tick duration
     *
     * @return void
     */
    protected function getTickDuration()
    {
        $result = null;
        if ($this->getOptions()->time && 1 < $this->getOptions()->position) {
            $result = $this->getOptions()->time / $this->getOptions()->position;

        } else {
            $tick = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getVar($this->getImportTickDurationVarName());
            if ($tick) {
                $result = $tick;
            }
        }

        return $result ? (ceil($result * 1000) / 1000) : static::DEFAUL_TICK_DURATION;
    }

    /**
     * Check - import process has errors or not
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return $this->importer->hasErrors();
    }

    /**
     * Check - import process has warnings or not
     *
     * @return boolean
     */
    public function hasWarnings()
    {
        return $this->importer->hasWarnings();
    }

    /**
     * Finalize
     *
     * @return void
     */
    public function finalize()
    {
        \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->setVar(
            $this->getImportTickDurationVarName(),
            $this->count() ? round($this->getOptions()->time / $this->count(), 3) : 0
        );
    }

    // {{{ SeekableIterator, Countable

    /**
     * \SeekableIterator::seek
     *
     * @param integer $position Position
     *
     * @return void
     */
    public function seek($position)
    {
        if ($this->getOptions()->position != $position && $position <= $this->count()) {
            $this->getOptions()->position = $position;
        }
    }

    /**
     * \SeekableIterator::current
     *
     * @return \XLite\Logic\Import\Processor\AProcessor
     */
    public function current()
    {
        return $this;
    }

    /**
     * \SeekableIterator::key
     *
     * @return integer
     */
    public function key()
    {
        return $this->getOptions()->position;
    }

    /**
     * \SeekableIterator::next
     *
     * @return void
     */
    public function next()
    {
        $this->seek($this->key() + 1);
    }

    /**
     * \SeekableIterator::rewind
     *
     * @return void
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * \SeekableIterator::valid
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->getProcessor()
            && $this->getProcessor()->valid()
            && $this->key() < $this->count();
    }

    /**
     * \Counable::count
     *
     * @return integer
     */
    public function count()
    {
        $result = 0;
        foreach ($this->importer->getProcessors() as $processor) {
            $result += $processor->count();
        }

        return $result;
    }

    /**
     * Get importTickDuration TmpVar name
     *
     * @return string
     */
    protected function getImportTickDurationVarName()
    {
        return 'importTickDuration';
    }

    // }}}
}
