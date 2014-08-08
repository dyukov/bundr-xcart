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

namespace XLite\View\Button;

/**
 * Link as link
 */
class BackToModulesLink extends \XLite\View\Button\SimpleLink
{
    /**
     * Widget params
     */
    const PARAM_MODULE_ID = 'moduleId';

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_MODULE_ID => new \XLite\Model\WidgetParam\String('Module ID', ''),
        );
    }

    /**
     * We make the full location path for the provided URL
     *
     * @return string
     */
    protected function getLocationURL()
    {
        $repo = \XLite\Core\Database::getRepo('XLite\Model\Module');
        $module = $repo->find($this->getParam(static::PARAM_MODULE_ID));

        $params = array(
            'clearCnd' => 1,
            'pageId' => \XLite\Core\Database::getRepo('XLite\Model\Module')->getModulePageId(
                $module->getAuthor(),
                $module->getName(),
                \XLite\View\Pager\Admin\Module\Manage::getInstance()->getItemsPerPage()
            ),
        );

        return \XLite::getInstance()->getShopURL($this->buildURL('addons_list_installed', '', $params) . '#' . $module->getName());
    }

    /**
     * getDefaultLabel
     *
     * @return string
     */
    protected function getDefaultLabel()
    {
        return 'Back to modules';
    }
}
