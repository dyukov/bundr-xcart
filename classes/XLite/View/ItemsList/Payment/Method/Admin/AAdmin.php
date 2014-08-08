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

namespace XLite\View\ItemsList\Payment\Method\Admin;

/**
 * Abstract admin-based payment methods list
 */
abstract class AAdmin extends \XLite\View\ItemsList\AItemsList
{
    /**
     * Payment method
     *
     * @var \XLite\Model\Payment\Method
     */
    protected $paymentMethod;

    /**
     * Has admin icon or not
     *
     * @var boolean
     */
    protected $hasAdminIcon = false;

    /**
     * Returns a list of CSS classes (separated with a space character) to be attached to the items list
     *
     * @return string
     */
    public function getListCSSClasses()
    {
        return parent::getListCSSClasses() . ' methods';
    }

    /**
     * Return dir which contains the page body template
     *
     * @return string
     */
    protected function getPageBodyDir()
    {
        return 'payment/methods';
    }

    /**
     * Return class name for the list pager
     *
     * @return string
     */
    protected function getPagerClass()
    {
        return '\XLite\View\Pager\Admin\Model\Infinity';
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $cnd = parent::getSearchCondition();

        $cnd->{\XLite\Model\Repo\Payment\Method::P_MODULE_ENABLED} = true;
        $cnd->{\XLite\Model\Repo\Payment\Method::P_ADDED} = true;
        $cnd->{\XLite\Model\Repo\Payment\Method::P_ORDERBY} = array('m.class, translations.name', 'asc');

        return $cnd;
    }

    /**
     * Return products list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $result = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')->search($cnd, $countOnly);

        if (!$countOnly) {
            $icons = array();
            $plain = array();
            foreach ($result as $method) {
                if ($method->getAdminIconURL()) {
                    $icons[] = $method;

                } else {
                    $plain[] = $method;
                }
            }

            $result = array_merge($icons, $plain);
        }

        return $result;
    }

    // {{{ Content helpers

    /**
     * Get line class
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    protected function getLineClass(\XLite\Model\Payment\Method $method)
    {
        $classes = array('cell');

        if ($method->getAdminIconURL()) {
            $classes[] = $this->hasAdminIcon($method) ? 'has-icon' : 'has-not-icon';
        }

        if (!$this->canSwitch($method)) {
            $classes[] = 'blocked-switch';
            $classes[] = $this->canEnable($method) ? 'blocked-disable' : 'blocked-enable';

        } elseif ($method->getWarningNote() && !$method->isEnabled()) {
            $classes[] = 'blocked-switch';
            $classes[] = 'blocked-enable';
        }

        return implode(' ', $classes);
    }

    /**
     * Check - can switch method or not
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    protected function canSwitch(\XLite\Model\Payment\Method $method)
    {
        return $method->isEnabled() ? !$method->isForcedEnabled() : $method->canEnable();
    }

    /**
     * Check - can enable method or not
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    protected function canEnable(\XLite\Model\Payment\Method $method)
    {
        return $method->canEnable();
    }

    /**
     * Get note with explanation why payment method can not be disabled
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    protected function getForbidDisableNote(\XLite\Model\Payment\Method $method)
    {
        return $method->getForcedEnabledNote();
    }

    /**
     * Get note with explanation why payment method can not be enabled
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return void
     */
    protected function getForbidEnableNote(\XLite\Model\Payment\Method $method)
    {
        return $method->getForbidEnableNote();
    }

    /**
     * Check - method can remove or not
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    protected function canRemoveMethod(\XLite\Model\Payment\Method $method)
    {
        return !($method->getProcessor() instanceOf \XLite\Model\Payment\Processor\Offline)
            || get_class($method->getProcessor()) == 'XLite\Model\Payment\Processor\Offline';
    }

    /**
     * Check - display right action box or not
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    protected function hasRightActions(\XLite\Model\Payment\Method $method)
    {
        return !$method->isForcedEnabled() && ($this->canRemoveMethod($method) || $method->getWarningNote() || $method->isConfigurable());
    }

    /**
     * Check - display separate Configure button or not
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    protected function isSeparateConfigureButtonVisible(\XLite\Model\Payment\Method $method)
    {
        return ($method->getWarningNote() || $method->isTestMode())
            && $method->isConfigurable()
            && !$method->isForcedEnabled();
    }

    /**
     * Method has admin icon URL or not
     *
     * @var \XLite\View\Payment\Method
     *
     * @return boolean
     */
    protected function hasAdminIconURL(\XLite\View\Payment\Method $method)
    {
        $result = $method->getAdminIconURL() && $this->adminIconURL != $method->getAdminIconURL();
        $this->adminIconURL = $method->getAdminIconURL();

        return $result;
    }

    /**
     * Method has admin icon URL or not
     *
     * @var \XLite\Model\Payment\Method
     *
     * @return boolean
     */
    protected function hasAdminIcon(\XLite\Model\Payment\Method $method)
    {
        if (
            !isset($this->paymentMethod)
            || $this->paymentMethod->getMethodId() != $method->getMethodId()
        ) {
            $this->hasAdminIcon = $method->getAdminIconURL()
                && (
                    !isset($this->paymentMethod)
                    || $this->paymentMethod->getAdminIconURL() != $method->getAdminIconURL()
            );
            $this->paymentMethod = $method;
        }

        return $this->hasAdminIcon;
    }

    // }}}

}
