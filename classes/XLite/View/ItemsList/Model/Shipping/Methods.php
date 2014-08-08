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

namespace XLite\View\ItemsList\Model\Shipping;

/**
 * Shipping methods list
 */
class Methods extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Widget param names
     */
    const PARAM_CARRIER = 'carrier';


    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Model\Shipping\Method';
    }

    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = array(
            'name' => array(
                static::COLUMN_NAME  => static::t('Shipping method'),
                static::COLUMN_CLASS => !$this->getProcessor() || $this->getProcessor()->isMethodNamesAdjustable()
                    ? 'XLite\View\FormField\Inline\Input\Text'
                    : null,
                static::COLUMN_ORDERBY  => 100,
            ),
            'taxClass' => array(
                static::COLUMN_NAME   => static::t('Tax class'),
                static::COLUMN_CLASS  => 'XLite\View\FormField\Inline\Select\TaxClass',
                static::COLUMN_PARAMS => array(
                    'fieldOnly' => true,
                ),
                static::COLUMN_ORDERBY  => 200,
            ),
        );

        if (!$this->getProcessor() || 'offline' == $this->getProcessor()->getProcessorId()) {
            $columns['rates'] = array(
                static::COLUMN_NAME   => static::t('Rates'),
                static::COLUMN_TEMPLATE  => false,
                static::COLUMN_ORDERBY  => 300,
            );
        }

        return $columns;
    }

    /**
     * Get list name suffixes
     *
     * @return array
     */
    protected function getListNameSuffixes()
    {
        return array('shipping-methods');
    }

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' shipping-methods';
    }

    /**
     * Get create button label
     *
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        return 'Add shipping method';
    }

    /**
     * Creation button position
     *
     * @return integer
     */
    protected function isCreation()
    {
        return (!$this->getProcessor() || $this->getProcessor()->isMethodDeleteEnabled()) ? static::CREATE_INLINE_TOP : static::CREATE_INLINE_NONE;
    }

    /**
     * Inline creation mechanism position
     *
     * @return integer
     */
    protected function isInlineCreation()
    {
        return (!$this->getProcessor() || $this->getProcessor()->isMethodDeleteEnabled()) ? static::CREATE_INLINE_TOP : static::CREATE_INLINE_NONE;
    }

    /**
     * Mark list as sortable
     *
     * @return integer
     */
    protected function getSortableType()
    {
        return static::SORT_TYPE_MOVE;
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
     * Mark list as removable
     *
     * @return boolean
     */
    protected function isRemoved()
    {
        return !$this->getProcessor() || $this->getProcessor()->isMethodDeleteEnabled();
    }

    /**
     * Mark list as selectable
     *
     * @return boolean
     */
    protected function isSelectable()
    {
        return false;
    }

    /**
     * Get translation language code
     *
     * @return string
     */
    protected function getCarrier()
    {
        return \XLite::getController()->getCarrier();
    }

    /**
     * Return true if shipping methods' name can be edited
     *
     * @return boolean
     */
    protected function getProcessor()
    {
        $result = null;

        $list = \XLite\Model\Shipping::getProcessors();

        $carrier = $this->getCarrier() ?: 'offline';

		foreach ($list as $p) {
            if ($p->getProcessorId() == $carrier) {
                $result = $p;
                break;
            }
		}

        return $result;
    }

    // {{{ Search

    /**
     * Return search parameters
     *
     * @return array
     */
    static public function getSearchParams()
    {
        return array(
            \XLite\Model\Repo\Shipping\Method::P_CARRIER => static::PARAM_CARRIER,
        );
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_CARRIER => new \XLite\Model\WidgetParam\String('Carrier code', ''),
        );
    }

    /**
     * Define so called "request" parameters
     *
     * @return void
     */
    protected function defineRequestParams()
    {
        parent::defineRequestParams();

        $this->requestParams = array_merge($this->requestParams, static::getSearchParams());
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $result = parent::getSearchCondition();

        foreach (static::getSearchParams() as $modelParam => $requestParam) {
            $result->$modelParam = $this->getParam($requestParam);
        }

        $carrierParam = \XLite\Model\Repo\Shipping\Method::P_CARRIER;

        if (!empty($result->{$carrierParam}) && 'offline' == $result->{$carrierParam}) {
            $result->{$carrierParam} = '';
        }

        $result->{\XLite\Model\Repo\Shipping\Method::P_ORDER_BY} = array('m.position', 'ASC');

        return $result;
    }

    // }}}

    /**
     * Get create message
     *
     * @param integer $count Count
     *
     * @return string
     */
    protected function getCreateMessage($count)
    {
        return \XLite\Core\Translation::lbl('X shipping methods have been created', array('count' => $count));
    }

    /**
     * Get remove message
     *
     * @param integer $count Count
     *
     * @return string
     */
    protected function getRemoveMessage($count)
    {
        return \XLite\Core\Translation::lbl('X shipping methods have been removed', array('count' => $count));
    }

    /**
     * Create entity
     *
     * @return \XLite\Model\AEntity
     */
    protected function createEntity()
    {
        $entity = parent::createEntity();

        // Set up processor
        $entity->setProcessor($this->getCarrier() ?: 'offline');

        // Set up carrier if it's not 'offline'
        $entity->setCarrier($this->getCarrier() && 'offline' != $this->getCarrier() ? $this->getCarrier() : '');

        // Enable new method
        $entity->setEnabled(true);

        return $entity;
    }

    /**
     * Get pager parameters
     *
     * @return array
     */
    protected function getPagerParams()
    {
        $params = parent::getPagerParams();

        $params[\XLite\View\Pager\APager::PARAM_ITEMS_PER_PAGE] = 50;

        return $params;
    }

    /**
     * Get template name to display when list is empty
     *
     * @return string
     */
    protected function getEmptyListTemplate()
    {
        return $this->getDir() . '/' . $this->getPageBodyDir() . '/shipping/methods/empty.tpl';
    }
}
