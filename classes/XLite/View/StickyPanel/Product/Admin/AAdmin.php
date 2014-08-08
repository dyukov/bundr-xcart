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

namespace XLite\View\StickyPanel\Product\Admin;

/**
 * Abstract product panel for admin interface
 */
abstract class AAdmin extends \XLite\View\StickyPanel\Product\AProduct
{
    /**
     * Define additional buttons
     *
     * @return array
     */
    protected function defineAdditionalButtons()
    {
        $list = parent::defineAdditionalButtons();

        $list[] = $this->getWidget(
            array(
                'disabled'   => true,
                'label'      => 'Delete',
                'style'      => 'more-action',
                'icon-style' => 'fa fa-trash-o',
            ),
            'XLite\View\Button\DeleteSelectedProducts'
        );

        $list[] = $this->getWidget(
            array(),
            'XLite\View\Button\Divider'
        );

        $list[] = $this->getWidget(
            array(
                'disabled'   => true,
                'label'      => 'Clone',
                'style'      => 'more-action',
                'icon-style' => 'fa fa-copy',
            ),
            'XLite\View\Button\CloneSelected'
        );

        $list[] = $this->getWidget(
            array(
                'disabled'   => true,
                'label'      => 'Enable',
                'style'      => 'more-action',
                'icon-style' => 'fa fa-power-off state-on',
            ),
            'XLite\View\Button\EnableSelected'
        );

        $list[] = $this->getWidget(
            array(
                'disabled'   => true,
                'label'      => 'Disable',
                'style'      => 'more-action',
                'icon-style' => 'fa fa-power-off state-off',
            ),
            'XLite\View\Button\DisableSelected'
        );

        return $list;
    }
}
