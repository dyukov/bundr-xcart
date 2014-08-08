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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\XC\Add2CartPopup\View\Model;

/**
 * General settings widget extention
 */
class Settings extends \XLite\View\Model\Settings implements \XLite\Base\IDecorator
{
    /**
     * Get CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'modules/XC/Add2CartPopup/style.css';

        return $list;
    }

    /**
     * Get form field by option
     *
     * @param \XLite\Model\Config $option Option
     *
     * @return array
     */
    protected function getFormFieldByOption(\XLite\Model\Config $option)
    {
        $cell = parent::getFormFieldByOption($option);

        if ('redirect_to_cart' == $option->getName() && $cell) {
            $limit = \XLite\View\Pager\Admin\Module\Manage::getInstance()->getItemsPerPage();
            $pageId = \XLite\Core\Database::getRepo('XLite\Model\Module')->getModulePageId('XC', 'Add2CartPopup', $limit);
            $params = array(
                'clearCnd' => 1,
                'page'     => $pageId,
            );
            $url = $this->buildURL('addons_list_installed', '', $params) . '#' . 'Add2CartPopup';

            $cell[static::SCHEMA_COMMENT] = static::t(
                "This option is ignored as Add to Cart Popup module is installed and enabled.",
                array('url' => $url)
            );
        }

        return $cell;
    }
}
