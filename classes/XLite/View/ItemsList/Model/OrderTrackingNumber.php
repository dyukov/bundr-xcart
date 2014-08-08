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

namespace XLite\View\ItemsList\Model;

/**
 * Order tracking number items list
 *
 * @ListChild (list="order.actions", weight="9999", zone="admin")
 */
class OrderTrackingNumber extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Widget parameter name
     */
    const PARAM_ORDER_ID = 'orderId';

    /**
     * Defines the CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'order/page/tracking.css';

        return $list;
    }

    /**
     * Defines the CSS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = 'order/page/tracking.js';

        return $list;
    }

    /**
     * Quick process
     *
     * @param array $parameters Parameters OPTIONAL
     *
     * @return void
     */
    public function processQuick(array $parameters = array())
    {
        $data = \XLite\Core\Request::getInstance()->getData();

        $new = $data['new'];
        unset($new[0]);
        $update = isset($data['data']) ? $data['data'] : array();
        $delete = isset($data['delete']) ? $data['delete'] : array();

        // Prepare information about the added numbers
        $added = array();
        foreach ($new as $id => $value) {
            $added[$id] = $value['value'];
        }

        // Prepare information about removed numbers (we remove them from the changed ones)
        $removed = array();
        foreach ($delete as $id => $value) {
            $removed[$id] = $update[$id]['value'];
            unset($update[$id]);
        }

        // Prepare information about changed numbers
        $changed = array();
        $repo = \XLite\Core\Database::getRepo('XLite\Model\OrderTrackingNumber');
        foreach ($update as $id => $value) {
            $oldValue = $repo->find($id)->getValue();
            if ($oldValue !== $value['value']) {
                $changed[$id] = array(
                    'old' => $oldValue,
                    'new' => $value['value'],
                );
            }
        }

        if ($added || $removed || $changed) {
            \XLite\Core\OrderHistory::getInstance()->registerTrackingInfoUpdate($this->getOrder()->getOrderId(), $added, $removed, $changed);
        }

        parent::processQuick($parameters);
    }

    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = array(
            'value' => array(
                static::COLUMN_CLASS  => 'XLite\View\FormField\Inline\Input\Text',
                static::COLUMN_PARAMS => array('required' => true),
                static::COLUMN_MAIN   => true,
                static::COLUMN_NAME   => static::t('Tracking number'),
                static::COLUMN_ORDERBY  => 100,
            ),
        );

        if ($this->getOrder()->getTrackingInformationURL('')) {
            $columns['track'] = array(
                static::COLUMN_NAME     => static::t('Payment status'),
                static::COLUMN_LINK     => 'track',
                static::COLUMN_TEMPLATE => $this->getDir() . '/' . $this->getPageBodyDir() . '/order_tracking_number/cell.track.tpl',
                static::COLUMN_ORDERBY  => 200,
            );
        }

        return $columns;
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Model\OrderTrackingNumber';
    }

    /**
     * Get create entity URL
     *
     * @return string
     */
    protected function getCreateURL()
    {
        return $this->buildURL('order');
    }

    /**
     * Get create button label
     *
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        return 'Add tracking number';
    }

    /**
     * Inline creation mechanism position
     *
     * @return integer
     */
    protected function isInlineCreation()
    {
        return static::CREATE_INLINE_TOP;
    }


    // {{{ Behaviors

    /**
     * Mark list as switchable (enable / disable)
     *
     * @return boolean
     */
    protected function isSwitchable()
    {
        return false;
    }

    /**
     * Mark list as removable
     *
     * @return boolean
     */
    protected function isRemoved()
    {
        return true;
    }

    // }}}

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' tracking-number';
    }

    /**
     * Check - sticky panel is visible or not
     *
     * @return boolean
     */
    protected function isPanelVisible()
    {
        return false;
    }

    // {{{ Search

    /**
     * Define widget params
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_ORDER_ID => new \XLite\Model\WidgetParam\Int(
                'OrderID ', ($this->getOrder() ? $this->getOrder()->getOrderId() : null)
            ),
        );
    }

    // {{{ Search

    /**
     * Return search parameters.
     *
     * @return array
     */
    static public function getSearchParams()
    {
        return array(
            static::PARAM_ORDER_ID => static::PARAM_ORDER_ID,
        );
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $result = parent::getSearchCondition();

        $result->{\XLite\Model\Repo\OrderTrackingNumber::P_ORDER_ID} = $this->getParam(static::PARAM_ORDER_ID);

        foreach (static::getSearchParams() as $modelParam => $requestParam) {
            $paramValue = $this->getParam($requestParam);

            if ('' !== $paramValue && 0 !== $paramValue) {
                $result->$modelParam = $paramValue;
            }
        }

        return $result;
    }

    /**
     * Create entity
     *
     * @return \XLite\Model\AEntity
     */
    protected function createEntity()
    {
        $entity = parent::createEntity();

        $entity->setOrder($this->getOrder());

        return $entity;
    }

    /*
     * getEmptyListTemplate
     *
     * @return string
     */
    protected function getEmptyListTemplate()
    {
        return 'order/page/no_tracking_numbers.tpl';
    }

    /**
     * Check - table header is visible or not
     *
     * @return boolean
     */
    protected function isTableHeaderVisible()
    {
        return false;
    }

    /**
     * Get top actions
     *
     * @return array
     */
    protected function getTopActions()
    {
        $actions = parent::getTopActions();

        // "Send tracking information" button is visible if the tracking numbers are provided
        if (!$this->isEmptyListTemplateVisible()) {
            $actions[] = 'order/page/parts/send_tracking.tpl';
        }

        return $actions;
    }

    // }}}

}
