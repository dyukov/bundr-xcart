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

namespace XLite\View\StickyPanel\ItemsList;

/**
 * Install modules list's sticky panel
 */
class InstallModules extends \XLite\View\StickyPanel\ItemsListForm
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
     * Return CSS files for the widget
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'modules_manager/css/install_modules.css';

       return $list;
    }

    /**
     * The sticky panel is visible if the items list has any result or some modules are to install
     *
     * @return boolean
     */
    protected function isVisible()
    {
        $list = new \XLite\View\ItemsList\Module\Install;

        return parent::isVisible() && ($list->hasResultsPublic() || ($this->countModulesSelected() > 0));
    }

    /**
     * Get "save" widget
     *
     * @return \XLite\View\Button\Submit
     */
    protected function getSaveWidget()
    {
        $modules = $this->getModulesToInstall();

        return $this->getWidget(
            array(
                'style'    => 'action submit',
                'label'    => static::t('Install modules'),
                'disabled' => empty($modules),
            ),
            // LAs of the modules popup button
            'XLite\View\Button\Addon\InstallModules'
        );
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
                'label'    => 'Empty',
                'style'    => 'action link',
            ),
            'XLite\View\Button\Addon\InstallModulesSelected'
        );

        return $list;
    }

    /**
     * Flag to display OR label
     *
     * @return boolean
     */
    protected function isDisplayORLabel()
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
        return static::t(
            'X module(s) selected',
            array('count' => $this->countModulesSelected())
        );
    }

    /**
     * Returns "more actions" specific label for bubble context window
     *
     * @return string
     */
    protected function getMoreActionsPopupText()
    {
        return $this->getMoreActionsText();
    }
}
