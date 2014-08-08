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

namespace XLite\View;

/**
 * Bread crumbs widget
 *
 * @ListChild (list="layout.main.breadcrumb", zone="customer", weight="100")
 */
class Location extends \XLite\View\AView
{
    /**
     * Widget param names
     */
    const PARAM_NODES = 'nodes';

    /**
     * Return breadcrumbs
     *
     * @return array
     */
    public function getNodes()
    {
        $list = array_values($this->getParam(static::PARAM_NODES));

        $list[count($list) - 1]->setWidgetParams(
            array(
                \XLite\View\Location\Node::PARAM_IS_LAST => true,
            )
        );

        return $list;
    }

    /**
     * Get a list of CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'location/location.css';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'location.tpl';
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
            static::PARAM_NODES => new \XLite\Model\WidgetParam\Collection('Breadcrumbs', $this->getLocationPath()),
        );
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && 1 < count($this->getNodes())
            && !$this->isCheckoutLayout()
            && \XLite::TARGET_404 != $this->getTarget();
    }
}
