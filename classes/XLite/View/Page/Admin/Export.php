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
 * Export page
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class Export extends \XLite\View\AView
{
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();

        $list[] = 'export';

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

        $list[] = 'export/style.css';

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

        $list[] = 'export/controller.js';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'export/page.tpl';
    }

    /**
     * Get inner widget class name
     * 
     * @return string
     */
    protected function getInnerWidget()
    {
        $result = 'XLite\View\Export\Begin';

        if ($this->isExportNotFinished()) {
            $result = 'XLite\View\Export\Progress';

        } elseif ($this->isExportFinished()) {
            $result = 'XLite\View\Export\Completed';

        } elseif ($this->isExportFailed()) {
            $result = 'XLite\View\Export\Failed';
        }

        return $result;
    }

    /**
     * Check - export process is not-finished or not 
     * 
     * @return boolean
     */
    protected function isExportNotFinished()
    {
        $state = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getEventName());

        return $state
            && in_array($state['state'], array(\XLite\Core\EventTask::STATE_STANDBY, \XLite\Core\EventTask::STATE_IN_PROGRESS))
            && !\XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getVar($this->getExportCancelFlagVarName());
    }

    /**
     * Check - export process is finished 
     * 
     * @return boolean
     */
    protected function isExportFinished()
    {
        $state = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getEventName());

        return $state
            && \XLite\Core\EventTask::STATE_FINISHED == $state['state']
            && \XLite\Core\Request::getInstance()->completed
            && !\XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getVar($this->getExportCancelFlagVarName());
    }

    /**
     * Check - export process is finished
     *
     * @return boolean
     */
    protected function isExportFailed()
    {
        $state = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getEventName());

        return $state
            && \XLite\Core\EventTask::STATE_ABORTED == $state['state']
            && \XLite\Core\Request::getInstance()->failed
            && !\XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getVar($this->getExportCancelFlagVarName())
            && $this->getGenerator()
            && $this->getGenerator()->hasErrors();
    }

    /**
     * Get export event name
     *
     * @return string
     */
    protected function getEventName()
    {
        return \XLite\Logic\Export\Generator::getEventName();
    }

    /**
     * Get export cancel flag name
     *
     * @return string
     */
    protected function getExportCancelFlagVarName()
    {
        return \XLite\Logic\Export\Generator::getExportCancelFlagVarName();
    }
}

