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

namespace XLite\View\Pager\Admin\Module;

/**
 * Pager for the marketplace page
 */
class Install extends \XLite\View\Pager\Admin\Module\AModule
{
    /**
     * What's new title text
     */
    const WHATS_NEW_TITLE = 'What\'s new';

    /**
     * getItemsPerPageDefault
     *
     * @return integer
     */
    protected function getItemsPerPageDefault()
    {
        return 15;
    }

    /**
     * Do not show pager on bottom
     *
     * @return boolean
     */
    protected function isVisibleBottom()
    {
        return true;
    }

    /**
     * Return current list name
     *
     * @return string
     */
    protected function getListName()
    {
        return 'install-modules.pager';
    }

    /**
     * Define the pager title
     *
     * @return string
     */
    protected function getPagerTitle()
    {
        return $this->isLandingPage()
            ? static::t(static::WHATS_NEW_TITLE)
            : parent::getPagerTitle();
    }

    /**
     * Define the pager bottom title
     *
     * @return string
     */
    protected function getPagerBottomTitle()
    {
        return '<a href="' . $this->buildURL('addons_list_marketplace') . '">' . static::t('View all modules') . '</a>';
    }
}
