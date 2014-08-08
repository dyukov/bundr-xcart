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

namespace XLite\View\StickyPanel\Language;

/**
 * Language items list panel for admin interface
 */
class Admin extends \XLite\View\StickyPanel\ItemsListForm
{
    /**
     * Check panel has more actions buttons
     *
     * @return boolean 
     */
    protected function hasMoreActionsButtons()
    {
        return false;
    }

    /**
     * Returns "more actions" specific label
     * 
     * @return string
     */
    protected function getMoreActionsText()
    {
        return static::t('Add new language');
    }
    
    /**
     * Returns "more actions" specific label for bubble context window
     * 
     * @return string
     */
    protected function getMoreActionsPopupText()
    {
        return static::t('Add new language');
    }
 
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
                'disabled' => false,
                'label'    => 'Find language in marketplace',
                'style'    => 'action link always-enabled',
                'location' => $this->buildURL('addons_list_marketplace', '', array('tag' => 'Translation')),
            ),
            'XLite\View\Button\Link'
        );

        $list[] = $this->getWidget(
            array(
                'disabled'   => false,
                'label'      => 'Import language from CSV file',
                'style'      => 'action link always-enabled',
                'object'     => 'language',
                'fileObject' => 'file',
            ),
            '\XLite\View\Button\FileSelector'
        );

        $list[] = $this->getWidget(
            array(
                'disabled' => false,
                'label'    => 'Add language',
                'style'    => 'action link always-enabled',
            ),
            'XLite\View\LanguagesModify\Button\AddNewLanguage'
        );

        return $list;
    }
}
