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

namespace XLite\Module\XC\Add2CartPopup\View;

/**
 * Add2CartPopup module settings page widget 
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class Admin extends \XLite\View\Dialog
{
    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();
        $result[] = 'add2_cart_popup';

        return $result;
    }

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/style.css';

        return $list;
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/XC/Add2CartPopup';
    }

    /**
     * Get promotion message
     *
     * @return string
     */
    protected function getPromotionMessage()
    {
        $addons = $this->getAddons();
        $modules = array();

        $params = array('clearCnd' => 1);

        foreach ($addons as $addon => $title) {

            if (!\Includes\Utils\Operator::checkIfClassExists('\\XLite\\Module\\' . $addon . '\\Main')) {

                $match = explode('\\', $addon);

                if (\Includes\Utils\FileManager::isExists(LC_DIR_MODULES . str_replace('\\', '/', $addon) . '/Main.php')) {
                    // Module is installed but not enabled
                    $limit = \XLite\View\Pager\Admin\Module\Manage::getInstance()->getItemsPerPage();
                    $pageId = \XLite\Core\Database::getRepo('XLite\Model\Module')->getModulePageId($match[0], $match[1], $limit);
                    $pageParam = 'page';
                    $target = 'addons_list_installed';

                } else {
                    // Module is not installed
                    list($start, $limit) = $this->getWidget(array(), '\XLite\View\Pager\Admin\Module\Install')->getLimitCondition()->limit;
                    $pageId = \XLite\Core\Database::getRepo('XLite\Model\Module')->getPageId($match[0], $match[1], $limit);
                    $pageParam = 'pageId';
                    $target = 'addons_list_marketplace';
                }

                if (0 < $pageId) {
                    $params[$pageParam] = $pageId;
                }

                $url = $this->buildURL($target, '', $params) . '#' . $match[1];

                $modules[] = '<a href="' . $url . '">' . $title . '</a>';
            }
        }

        return (0 < count($modules))
            ? static::t('Install additional modules to add more product sources', array('list' => implode(', ', $modules)))
            : '';
    }

    /**
     * Get modules list which provide additional products sources for 'Add to Cart Popup' dialog
     *
     * @return array
     */
    protected function getAddons()
    {
        return array(
            'XC\\Upselling'        => 'Related Products',
            'CDev\\ProductAdvisor' => 'Product Advisor',
        );
    }
}
