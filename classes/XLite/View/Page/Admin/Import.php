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

namespace XLite\View\Page\Admin;

/**
 * Import page
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class Import extends \XLite\View\AView
{
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();

        $list[] = 'import';

        return $list;
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'import/style.css';

        return $list;
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'import/controller.js';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'import/page.tpl';
    }

    /**
     * Get inner widget class name
     *
     * @return string
     */
    protected function getInnerWidget()
    {
        $result = 'XLite\View\Import\Begin';

        if ($this->isImportNotFinished()) {
            $result = 'XLite\View\Import\Progress';

        } elseif ($this->isImportFailed()) {
            $result = 'XLite\View\Import\Failed';

        } elseif ($this->isImportFinished()) {
            $result = 'XLite\View\Import\Completed';
        }

        return $result;
    }

    /**
     * Check - import process is not-finished or not
     *
     * @return boolean
     */
    protected function isImportNotFinished()
    {
        $state = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getEventName());

        return $state
            && in_array($state['state'], array(\XLite\Core\EventTask::STATE_STANDBY, \XLite\Core\EventTask::STATE_IN_PROGRESS))
            && !\XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getVar($this->getImportUserBreakFlagVarName())
            && !\XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getVar($this->getImportCancelFlagVarName());
    }

    /**
     * Check - import process is finished
     *
     * @return boolean
     */
    protected function isImportFinished()
    {
        $state = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getEventName());

        return \XLite\Core\Request::getInstance()->failed
            || (
                $state
                && \XLite\Core\EventTask::STATE_FINISHED == $state['state']
                && \XLite\Core\Request::getInstance()->completed
                && !\XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getVar($this->getImportCancelFlagVarName())
            );
    }

    /**
     * Check - import process is finished
     *
     * @return boolean
     */
    protected function isImportFailed()
    {
        $event = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getEventName());

        $result = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getVar($this->getImportUserBreakFlagVarName());

        if (!$result) {

            $result = $event
                && !\XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getVar($this->getImportCancelFlagVarName())
                && \XLite\Core\Request::getInstance()->completed
                && (
                    (
                        \XLite\Core\EventTask::STATE_FINISHED == $event['state']
                        && !$event['options']['step']
                    )
                    || \XLite\Core\EventTask::STATE_ABORTED == $event['state']
                )
                && (
                    \XLite\Logic\Import\Importer::hasErrors()
                    || \XLite\Logic\Import\Importer::hasWarnings()
                );
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

    /**
     * Get import cancel flag name
     *
     * @return string
     */
    protected function getImportCancelFlagVarName()
    {
        return \XLite\Logic\Import\Importer::getImportCancelFlagVarName();
    }

    /**
     * Get import user break flag name
     *
     * @return string
     */
    protected function getImportUserBreakFlagVarName()
    {
        return \XLite\Logic\Import\Importer::getImportUserBreakFlagVarName();
    }
}
