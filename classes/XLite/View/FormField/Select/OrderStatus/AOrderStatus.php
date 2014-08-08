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

namespace XLite\View\FormField\Select\OrderStatus;

/**
 * Abstract order status selector
 */
abstract class AOrderStatus extends \XLite\View\FormField\Select\Regular
{
    /**
     * Common params
     */
    const PARAM_ORDER = 'order';
    const PARAM_ALL_OPTION  = 'allOption';

    /**
     * Define repository name
     *
     * @return string
     */
    abstract protected function defineRepositoryName();

    /**
     * Return "all statuses" label
     *
     * @return string
     */
    abstract protected function getAllStatusesLabel();

    /**
     * Define widget params
     *
     * @return void
     */
    protected function getOrder()
    {
        return $this->getParam(self::PARAM_ORDER);
    }

    /**
     * Define widget params
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_ORDER => new \XLite\Model\WidgetParam\Object(
                'Order', null, false, '\\XLite\\\Model\\Order'
            ),
            self::PARAM_ALL_OPTION  => new \XLite\Model\WidgetParam\Bool(
                'Show "All status" option', false, false
            ),
        );
    }

    /**
     * Return default options list
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array();
    }

    /**
     * Define the options list
     *
     * @return array
     */
    protected function getOptions()
    {
        $data = \XLite\Core\Database::getRepo($this->defineRepositoryName())->findBy(
            array(),
            array('position' => 'asc')
        );
        $list = array();
        foreach ($data as $status) {
            if (
                !$this->getOrder()
                || $this->getParam(static::PARAM_ALL_OPTION)
                || $status->isAllowedToSetManually()
                || $status->getId() == $this->getValue()
            ) {
                $list[$status->getId()] = $status->getName();
            }
        }

        if ($this->getOrder() && !$this->getValue()) {
            $list = array(0 => static::t('Status is not defined')) + $list;

        } elseif ($this->getParam(static::PARAM_ALL_OPTION)) {
            $list = array(0 => static::t($this->getAllStatusesLabel())) + $list;
        }

        return $list;
    }
}
