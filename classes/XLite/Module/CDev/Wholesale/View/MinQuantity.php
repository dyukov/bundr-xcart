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

namespace XLite\Module\CDev\Wholesale\View;

/**
 * Minimum quantity for product
 */
class MinQuantity extends \XLite\View\AView
{
    /**
     * Widget parameter names
     */
    const PARAM_PRODUCT = 'product';

    /**
     * Minimum order quantity
     *
     * @var   integer
     */
    protected $minQuantity = null;

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/CDev/Wholesale/min_quantity/style.css';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/Wholesale/min_quantity/body.tpl';
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
                $this->getProduct(),
                false,
                '\XLite\Model\Product'
            ),
        );
    }

    /**
     * Check widget visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && $this->hasMinimumOrderQuantity();
    }

    /**
     * Return minimum quantity for ordering
     *
     * @return boolean
     */
    protected function hasMinimumOrderQuantity()
    {
        return $this->getMinimumOrderQuantity() > 1;
    }

    /**
     * Return minimum quantity for ordering
     *
     * @return integer
     */
    protected function getMinimumOrderQuantity()
    {
        if (is_null($this->minQuantity)) {
            $this->minQuantity = $this->getParam(self::PARAM_PRODUCT)->getMinQuantity(
                \XLite\Core\Auth::getInstance()->getProfile() ? \XLite\Core\Auth::getInstance()->getProfile()->getMembership() : null
            );
        }

        return $this->minQuantity;
    }
}
