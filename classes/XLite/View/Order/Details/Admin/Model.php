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

namespace XLite\View\Order\Details\Admin;

/**
 * Model
 */
class Model extends \XLite\View\Order\Details\Base\AModel
{
    /**
     * Main order info
     *
     * @var array
     */
    protected $schemaMain = array(
        'order_id' => array(
            self::SCHEMA_CLASS => '\XLite\View\FormField\Label',
            self::SCHEMA_LABEL => 'Order ID',
        ),
        'date' => array(
            self::SCHEMA_CLASS => '\XLite\View\FormField\Label',
            self::SCHEMA_LABEL => 'Order date',
        ),
        'paymentStatus' => array(
            self::SCHEMA_CLASS => '\XLite\View\FormField\Select\OrderStatus\Payment',
            self::SCHEMA_LABEL => 'Payment order status',
        ),
        'shippingStatus' => array(
            self::SCHEMA_CLASS => '\XLite\View\FormField\Select\OrderStatus\Shipping',
            self::SCHEMA_LABEL => 'Shipping order status',
        ),
    );


    /**
     * Save current form reference and sections list, and initialize the cache
     *
     * @param array $params   Widget params OPTIONAL
     * @param array $sections Sections list OPTIONAL
     *
     * @return void
     */
    public function __construct(array $params = array(), array $sections = array())
    {
        $this->sections['main'] = 'Info';

        parent::__construct($params, $sections);
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'order/invoice/style.css';

        return $list;
    }


    /**
     * Alias
     *
     * @return \XLite\Model\Order
     */
    protected function getOrder()
    {
        return $this->getModelObject();
    }

    /**
     * Format order date
     *
     * @param array &$data Widget params
     *
     * @return void
     */
    protected function prepareFieldParamsDate(array &$data)
    {
        $data[self::SCHEMA_VALUE] = $this->time_format($this->getModelObject()->getDate());
    }
}
