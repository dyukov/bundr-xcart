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

namespace XLite\Module\CDev\Egoods\Controller\Customer;

/**
 * Order list controller
 */
abstract class OrderList extends \XLite\Controller\Customer\OrderList implements \XLite\Base\IDecorator
{
    /**
     * Get pages sections
     *
     * @return array
     */
    public function getPages()
    {
        $list = parent::getPages();

        if ($this->getOrdersWithFiles()) {
            $list['files'] = 'Files';
        }

        return $list;
    }

    /**
     * Get pages templates
     *
     * @return array
     */
    protected function getPageTemplates()
    {
        $list = parent::getPageTemplates();

        $list['files'] = 'modules/CDev/Egoods/files.tpl';

        return $list;
    }

    /**
     * Get orders with files 
     * 
     * @return array
     */
    public function getOrdersWithFiles()
    {
        $profile = null;
        if ($this->getProfile()->isAdmin() && \XLite::isAdminZone()) {

            if (!empty(\XLite\Core\Request::getInstance()->profile_id)) {
                $profile = \XLite\Core\Database::getRepo('\XLite\Model\Profile')->find(\XLite\Core\Request::getInstance()->profile_id);
            }

        } else {

            $profile = $this->getProfile();
        }

        return \XLite\Core\Database::getRepo('\XLite\Model\Order')->findAllOrdersWithEgoods($profile);
    }

}

