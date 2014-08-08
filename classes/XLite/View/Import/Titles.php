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
 * Titles section
 */
class Titles extends \XLite\View\AView
{
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'import/parts/titles.tpl';
    }

    /**
     * Return titles
     *
     * @return array
     */
    protected function getTitles()
    {
        return array(
            0 => array(
                'text'    => 'Verified',
                'current' => 'Verifying data before importing...',
            ),
            1 => array(
                'text'    => 'Imported',
                'current' => 'Importing data...',
            ),
        );
    }

    /**
     * Return current titles
     *
     * @return array
     */
    protected function getCurrentTitles()
    {
        $result = $this->getTitles();
        $state = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getEventName());

        $step = $state && \XLite\Core\EventTask::STATE_FINISHED == $state['state']
            ? 9999
            : $this->getCurrentStep();

        foreach ($result as $k => $v) {
            if (
                $k <= $step
            ) {
                $result[$k]['class'] = $step == $k ? '' : 'completed';
                $result[$k]['text'] = $v[$step == $k ? 'current' : 'text'];

            } else {
                unset($result[$k]);
            }
        }

        return $result;
    }

    /**
     * Checks whether the widget is visible, or not
     *
     * @return boolean
     */
    protected function isVisible()
    {
        $result = parent::isVisible();

        if ($result) {

            $state = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($this->getEventName());

            $result = false;

            if ($state && \XLite\Core\EventTask::STATE_FINISHED == $state['state']) {

                $data = $state['options']['columnsMetaData'];

                if ($data) {
                    foreach (\XLite\Logic\Import\Importer::getProcessorList() as $processor) {
                        $addCount = isset($data[$processor]['addCount']) ? $data[$processor]['addCount'] : 0;
                        $updateCount = isset($data[$processor]['updateCount']) ? $data[$processor]['updateCount'] : 0;

                        if (isset($data[$processor]) && 0 < $addCount + $updateCount) {
                            $result = true;
                            break;
                        }
                    }
                }

            } elseif ($state) {
                $result = true;
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
