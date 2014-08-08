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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\CDev\XPaymentsConnector\View\ItemsList\Model;

/**
 * Saved credit cards items list
 */
class PaymentMethods extends \XLite\View\ItemsList\Model\Table
{

    // {{{ Definers

    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        $listTemplateDir = 'modules/CDev/XPaymentsConnector/settings/payment_methods/'; 

        return array(
            'name' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Payment method'),
                static::COLUMN_NO_WRAP  => true,
                static::COLUMN_MAIN     => true,
                static::COLUMN_TEMPLATE => $listTemplateDir . 'list.name.tpl',
                static::COLUMN_ORDERBY  => 100,
            ),
            'currency' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Currency'),
                static::COLUMN_NO_WRAP  => false,
                static::COLUMN_TEMPLATE => $listTemplateDir . 'list.currency.tpl',
                static::COLUMN_ORDERBY  => 200,
            ),
            'save_cards' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Save cards'),
                static::COLUMN_NO_WRAP  => true,
                static::COLUMN_TEMPLATE => $listTemplateDir . 'list.save_cards.tpl',
                static::COLUMN_ORDERBY  => 800,
            ),
        );
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Model\Payment\Method';
    }

    // }}}

    // {{{ Behaviors

    /**
     * Mark list as removable
     *
     * @return boolean
     */
    protected function isRemoved()
    {
        return false;
    }

    /**
     * Mark list as switchable (enable / disable)
     *
     * @return boolean
     */
    protected function isSwitchable()
    {
        return true;
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
     * Creation button position
     *
     * @return integer
     */
    protected function isCreation()
    {
        return static::CREATE_INLINE_NONE;
    }

    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return 'XLite\Module\CDev\XPaymentsConnector\View\StickyPanel\PaymentMethods';
    }

    // }}}

    // {{{ Search

    /**
     * Return search parameters.
     *
     * @return array
     */
    static public function getSearchParams()
    {
        return array();
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $cnd = parent::getSearchCondition();

        $cnd->{\XLite\Model\Repo\Payment\Method::P_CLASS}
            = 'Module\CDev\XPaymentsConnector\Model\Payment\Processor\XPayments';

        $cnd->{\XLite\Model\Repo\Payment\Method::P_ORDERBY} = array('translations.name', 'asc');

        return $cnd;
    }

    // }}}

    /**
     * Get column cell class
     *
     * @param array                $column Column
     * @param \XLite\Model\AEntity $entity Model OPTIONAL
     *
     * @return string
     */
    protected function getColumnClass(array $column, \XLite\Model\AEntity $entity = null)
    {
        return parent::getColumnClass($column, $entity);
    }

    /**
     * Get column value
     *
     * @param array                $column Column
     * @param \XLite\Model\AEntity $entity Model
     *
     * @return mixed
     */
    protected function getColumnValue(array $column, \XLite\Model\AEntity $entity)
    {
        if ('name' == $column[static::COLUMN_CODE]) {
            $result = parent::getColumnValue($column, $entity);
        }

        return $result;
    }

    /**
     * Check - 'save cards' options is enabled or not
     * 
     * @param \XLite\Model\AEntity $entity Entity
     *  
     * @return boolean
     */
    protected function isSaveCards(\XLite\Model\AEntity $entity)
    {
        return $entity->getSetting('saveCards') == 'Y';
    }

    /**
     * Check - 'save cards' options is enabled or not
     *
     * @param \XLite\Model\AEntity $entity Entity
     *
     * @return boolean
     */
    protected function getTransactionTypesList(\XLite\Model\AEntity $entity)
    {
        $transactionTypes = array(
            'auth'    => 'Authorize', 
            'sale'    => 'Sale', 
            'void'    => 'Void', 
            'capture' => 'Capture', 
            'refund'  => 'Refund',
        );

        foreach ($transactionTypes as $type => $name) {
            if (static::t('Yes') !== $this->getTransactionTypeStatus($entity, $type)) {
                unset($transactionTypes[$type]);
            }
        }

        return implode(', ', $transactionTypes);
    }

    /**
     * Check - 'save cards' options is enabled or not
     *
     * @param \XLite\Model\AEntity $entity Entity
     *
     * @return boolean
     */
    protected function getCurrency(\XLite\Model\AEntity $entity)
    {
        $currency = $entity->getSetting('currency')
            ? $entity->getSetting('currency')
            : \XLite\Core\Config::getInstance()->CDev->XPaymentsConnector->xpc_currency;

        return $currency;
    }
}
