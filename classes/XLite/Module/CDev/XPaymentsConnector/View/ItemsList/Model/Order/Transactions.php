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

namespace XLite\Module\CDev\XPaymentsConnector\View\ItemsList\Model\Order;

/**
 * Saved credit cards items list
 */
class Transactions extends \XLite\Module\CDev\XPaymentsConnector\View\ItemsList\Model\Order\ATable
{

    /**
     * Readable types 
     * 
     * @var array
     */
    protected $readableTypes = array(
        \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_AUTH          => 'Authorized',
        \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_SALE          => 'Charged',
        \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_CAPTURE       => 'Captured',
        \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_CAPTURE_PART  => 'Captured',
        \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_CAPTURE_MULTI => 'Captured',
        \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_VOID          => 'Voided',
        \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_VOID_PART     => 'Voided',
        \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_VOID_MULTI    => 'Voided',
        \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_REFUND        => 'Refunded',
        \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_REFUND_PART   => 'Refunded',
        \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_REFUND_MULTI  => 'Refunded',
    );

    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        return array(
            'date' => array(
                static::COLUMN_NAME    => \XLite\Core\Translation::lbl('Submitted'),
                static::COLUMN_NO_WRAP => true,
            ),
            'status' => array(
                static::COLUMN_NAME    => \XLite\Core\Translation::lbl('Status'),
                static::COLUMN_NO_WRAP => true,
            ),
            'amount' => array(
                static::COLUMN_NAME    => \XLite\Core\Translation::lbl('Amount'),
                static::COLUMN_NO_WRAP => true,
                static::COLUMN_MAIN    => true,
            ),
        );
    }

    /**
     * Get readable transaction type 
     * 
     * @param string $type Type
     *  
     * @return string
     */
    protected function getReadableTransactionType($type) 
    {
        return isset($this->readableTypes[$type]) ? $this->readableTypes[$type] : 'n/a';
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
        switch ($column[static::COLUMN_CODE]) {

            case 'date':
                $result = $this->formatTime($entity->getDate());
                break;

            case 'amount':
                $result = $this->formatPrice($entity->getValue());
                break;

            case 'status':
                $result = $entity->getDataCell('xpc_message') 
                    ? $entity->getDataCell('xpc_message')->getValue() 
                    : $this->getReadableTransactionType($entity->getType());

                break;

            default:
                $result = parent::getColumnValue($column, $entity);
                break;
        }

        return $result;
    }

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
        $class = parent::getColumnClass($column, $entity);

        if ('status' == $column[static::COLUMN_CODE]) {
            $class .= ' ' . strtolower($entity->getType());
        }

        return $class;
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Model\Payment\BackendTransaction';
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $cnd = parent::getSearchCondition();

        $class = '\XLite\Module\CDev\XPaymentsConnector\Model\Repo\Payment\BackendTransaction';
    
        $cnd->{$class::SEARCH_ORDER_ID} = $this->getOrder()->getOrderId();

        return $cnd;
    }

}
