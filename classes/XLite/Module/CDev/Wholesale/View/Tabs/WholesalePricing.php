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

namespace XLite\Module\CDev\Wholesale\View\Tabs;

/**
 * Tabs related to Wholesale pricing pages (Product modify section)
 */
class WholesalePricing extends \XLite\View\Tabs\ATabs
{
    /**
     * Widget parameter names
     */
    const PARAM_PRODUCT = 'product';


    /**
     * Description of tabs
     *
     * @var   array
     */
    protected $tabs = array(
        'prices' => array(
            'title'    => 'Price tiers',
            'template' => 'modules/CDev/Wholesale/pricing/prices/body.tpl',
        ),

        'minqty' => array(
            'title'    => 'Minimum purchase quantity',
            'template' => 'modules/CDev/Wholesale/pricing/min_qty/body.tpl',
        ),
    );

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'modules/CDev/Wholesale/pricing/style.css';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'common/tabs2.tpl';
    }

    /**
     * Returns tab URL
     *
     * @param string $target Tab target
     *
     * @return string
     */
    protected function buildTabURL($target)
    {
        return $this->buildURL(
            'product',
            '',
            array(
                'page'  => 'wholesale_pricing',
                'product_id'    => $this->getParam(self::PARAM_PRODUCT)->getProductId(),
                'spage' => $target
            )
        );
    }

    /**
     * Returns the current target
     *
     * @return string
     */
    protected function getCurrentTarget()
    {
        return \XLite\Core\Request::getInstance()->spage ?: 'prices';
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
            self::PARAM_PRODUCT => new \XLite\Model\WidgetParam\Object(
                'Product',
                null,
                false,
                '\XLite\Model\Product'
            ),
        );
    }
}
