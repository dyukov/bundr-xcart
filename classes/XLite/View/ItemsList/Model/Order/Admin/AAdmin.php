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

namespace XLite\View\ItemsList\Model\Order\Admin;

/**
 * Abstract admin order-based list
 */
abstract class AAdmin extends \XLite\View\ItemsList\Model\Order\AOrder
{
    /**
     * Action information cell names
     */
    const ACTION_NAME     = 'name';
    const ACTION_URL      = 'url';
    const ACTION_ACTION   = 'action';
    const ACTION_CLASS    = 'class';
    const ACTION_TEMPLATE = 'template';
    const ACTION_PARAMS   = 'parameters';

    /**
     * Get order actions
     *
     * @param \XLite\Model\Order $entity Order
     *
     * @return array
     */
    protected function getOrderActions(\XLite\Model\Order $entity)
    {
        $list = array();

        foreach ($this->defineOrderActions($entity) as $action) {
            $parameters = empty($action[static::ACTION_PARAMS]) || !is_array($action[static::ACTION_PARAMS])
                ? array()
                : $action[static::ACTION_PARAMS];
            $parameters['entity'] = $entity;

            // Build URL
            if (!empty($action[static::ACTION_ACTION]) && empty($action[static::ACTION_URL])) {
                $action[static::ACTION_URL] = \XLite\Core\Converter::buildURL(
                    'order',
                    $action[static::ACTION_ACTION],
                    array('order_number' => $entity->getOrderNumber())
                );
            }

            if (!isset($action[static::ACTION_CLASS]) && !isset($action[static::ACTION_TEMPLATE])) {

                // Define widget as link-button
                $action[static::ACTION_CLASS] = 'XLite\View\Button\Link';
                $parameters['label'] = $action[static::ACTION_NAME];
                $parameters['location'] = $action[static::ACTION_URL];

            } elseif (!empty($action[static::ACTION_CLASS])) {

                // Prepare widget parameters
                if (!empty($action[static::ACTION_URL])) {
                    $parameters['url'] = $action[static::ACTION_URL];
                }

                if (!empty($action[static::ACTION_ACTION])) {
                    $parameters['action'] = $action[static::ACTION_ACTION];
                }
            }

            if (!empty($action[static::ACTION_TEMPLATE])) {
                $parameters['template'] = $action[static::ACTION_TEMPLATE];
            }

            $list[] = empty($action[static::ACTION_CLASS])
                ? $this->getWidget($parameters)
                : $this->getWidget($parameters, $action[static::ACTION_CLASS]);
        }

        return $list;
    }

    /**
     * Returns true if order has allowed backend payment transactions
     *
     * @param \XLite\Model\Order $entity Order
     *
     * @return boolean
     */
    protected function hasPaymentActions(\XLite\Model\Order $entity)
    {
        $result = \Includes\Utils\ArrayManager::filterByKeys(
            $entity->getAllowedPaymentActions(),
            $this->getTransactionsFilter()
        );
        return !empty($result);
    }

    /**
     * Get list of transaction types to filter allowed backend transactions list
     *
     * @return void
     */
    protected function getTransactionsFilter()
    {
        return array('capture');
    }

    /**
     * Define order actions
     *
     * @param \XLite\Model\Order $entity Order
     *
     * @return array
     */
    protected function defineOrderActions(\XLite\Model\Order $entity)
    {
        $list = array();

        foreach ($entity->getAllowedActions() as $action) {
            $list[] = array(
                static::ACTION_ACTION => $action,
            );
        }

        return $list;
    }
    
    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    protected function checkACL()
    {
        return parent::checkACL()
            && \XLite\Core\Auth::getInstance()->isPermissionAllowed('manage orders');
    }
}
