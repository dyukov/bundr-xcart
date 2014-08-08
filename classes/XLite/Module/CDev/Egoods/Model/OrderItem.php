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

namespace XLite\Module\CDev\Egoods\Model;

/**
 * Order item 
 */
abstract class OrderItem extends \XLite\Model\OrderItem implements \XLite\Base\IDecorator
{
    /**
     * Order items
     *
     * @var   \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Module\CDev\Egoods\Model\OrderItem\PrivateAttachment", mappedBy="item", cascade={"all"})
     */
    protected $privateAttachments;

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        parent::__construct($data);

        $this->privateAttachments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get available download attachments 
     * 
     * @return array
     */
    public function getDownloadAttachments()
    {
        $list = array();

        if ($this->isAllowDownloadAttachments() && $this->getPrivateAttachments()) {
            foreach ($this->getPrivateAttachments() as $attachment) {
                if ($attachment->isAvailable()) {
                    $list[] = $attachment;
                }
            }
        }

        return $list;
    }

    /**
     * Create private attachments 
     * 
     * @return void
     */
    public function createPrivateAttachments()
    {
        // Remove old attachments
        foreach ($this->getPrivateAttachments() as $attachment) {
            \XLite\Core\Database::getEM()->remove($attachment);
        }
        $this->getPrivateAttachments()->clear();

        // Create attachments
        if ($this->getProduct() && $this->getProduct()->getId() && 0 < count($this->getProduct()->getAttachments())) {

            foreach ($this->getProduct()->getAttachments() as $attachment) {
                if ($attachment->getPrivate()) {
                    $private = new \XLite\Module\CDev\Egoods\Model\OrderItem\PrivateAttachment;
                    $this->addPrivateAttachments($private);
                    $private->setItem($this);
                    $private->setAttachment($attachment);
                    $private->setTitle($attachment->getPublicTitle());
                    $private->setBlocked(false);
                }
            }
        }
    }

    /**
     * Check attachments downloading availability
     * 
     * @return boolean
     */
    protected function isAllowDownloadAttachments()
    {
        return in_array(
            $this->getOrder()->getPaymentStatusCode(),
            array(
                \XLite\Model\Order\Status\Payment::STATUS_PAID,
                \XLite\Model\Order\Status\Payment::STATUS_PART_PAID
            )
        );
    }
}

