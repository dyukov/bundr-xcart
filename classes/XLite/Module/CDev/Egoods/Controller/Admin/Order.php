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

namespace XLite\Module\CDev\Egoods\Controller\Admin;

/**
 * Order controller
 */
abstract class Order extends \XLite\Controller\Admin\Order implements \XLite\Base\IDecorator
{
    // {{{ Actions

    /**
     * Block egood link
     * 
     * @return void
     */
    protected function doActionEgoodsBlock()
    {
        $id = \XLite\Core\Request::GetInstance()->attachment_id;
        $attachment = \XLite\Core\Database::getRepo('XLite\Module\CDev\Egoods\Model\OrderItem\PrivateAttachment')->find($id);
        if ($attachment) {
            $attachment->setBlocked(true);
            \XLite\Core\Database::getEM()->flush();
            \XLite\Core\TopMessage::addInfo('Download link is blocked');

        } else {
            \XLite\Core\TopMessage::addError('Download link did not found');
        }
    }

    /**
     * Renew egood link
     *
     * @return void
     */
    protected function doActionEgoodsRenew()
    {
        $id = \XLite\Core\Request::GetInstance()->attachment_id;
        $attachment = \XLite\Core\Database::getRepo('XLite\Module\CDev\Egoods\Model\OrderItem\PrivateAttachment')->find($id);
        if (!$attachment) {
            \XLite\Core\TopMessage::addError('Download link did not found');

        } elseif (!$attachment->isActive()) {
            \XLite\Core\TopMessage::addError('Download link is not active');

        } else {
            $attachment->renew();
            \XLite\Core\Database::getEM()->flush();
            \XLite\Core\Mailer::sendEgoodsLinks($attachment->getItem()->getOrder());
            \XLite\Core\TopMessage::addInfo('Download link is renew');
        }
    }

    // }}}

    // {{{ Tabs

    /**
     * Get pages sections
     *
     * @return array
     */
    public function getPages()
    {
        $list = parent::getPages();

        $order = $this->getOrder();
        if ($order && $order->getPrivateAttachments()) {
            $list['egoods'] = 'E-goods';
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

        $list['egoods'] = 'modules/CDev/Egoods/order.tpl';

        return $list;
    }

    // }}}

}
