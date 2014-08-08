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

namespace XLite\Module\CDev\Egoods\Model\OrderItem;

/**
 * Order item's private attachment
 *
 * @Entity
 * @Table  (name="order_item_private_attachments")
 */
class PrivateAttachment extends \XLite\Model\AEntity
{
    // {{{ Columns

    /**
     * Unique id
     *
     * @var   integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="uinteger")
     */
    protected $id;

    /**
     * Saved title
     *
     * @var   string
     *
     * @Column (type="string", length=255)
     */
    protected $title;

    /**
     * Key
     *
     * @var   string
     *
     * @Column (type="fixedstring", length=128)
     */
    protected $downloadKey = '';

    /**
     * Expire time (UNIX timestamp)
     *
     * @var   integer
     *
     * @Column (type="uinteger")
     */
    protected $expire = 0;

    /**
     * Attempts count
     *
     * @var   integer
     *
     * @Column (type="uinteger")
     */
    protected $attempt = 0;

    /**
     * Attempts limit
     *
     * @var   integer
     *
     * @Column (type="uinteger")
     */
    protected $attemptLimit = 0;

    /**
     * Blocked status
     *
     * @var   boolean
     *
     * @Column (type="boolean")
     */
    protected $blocked = true;

    // }}}

    // {{{ Associations

    /**
     * Item order
     *
     * @var   \XLite\Model\OrderItem
     *
     * @ManyToOne  (targetEntity="XLite\Model\OrderItem", inversedBy="privateAttachments")
     * @JoinColumn (name="item_id", referencedColumnName="item_id")
     */
    protected $item;

    /**
     * Item order
     *
     * @var   \XLite\Model\OrderItem
     *
     * @ManyToOne (targetEntity="XLite\Module\CDev\FileAttachments\Model\Product\Attachment", cascade={"merge", "detach"})
     * @JoinColumn (name="attachment_id", referencedColumnName="id")
     */
    protected $attachment;

    // }}}

    // {{{ Operator

    /**
     * Check atatchment availability
     *
     * @return boolean
     */
    public function isAvailable()
    {
        return $this->getAttachment()
            && $this->getDownloadKey()
            && $this->isOrderCompleted()
            && !$this->getBlocked()
            && !$this->isExpired()
            && !$this->isAttemptsEnded()
            && $this->getAttachment()->getStorage()->isFileExists();
    }

    /**
     * Check attachment activity
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->getItem()->getProduct()->getProductId()
            && $this->getAttachment()
            && $this->isOrderCompleted();
    }

    /**
     * Check order complete state
     *
     * @return boolean
     */
    public function isOrderCompleted()
    {
        return in_array(
            $this->getItem()->getOrder()->getPaymentStatusCode(),
            array(
                \XLite\Model\Order\Status\Payment::STATUS_PAID,
                \XLite\Model\Order\Status\Payment::STATUS_PART_PAID
            )
        );
    }

    /**
     * Check - has expire limit or not
     *
     * @return boolean
     */
    public function hasExpireLimit()
    {
        return 0 < $this->getExpire();
    }

    /**
     * Check expired status
     *
     * @return boolean
     */
    public function isExpired()
    {
        return $this->hasExpireLimit() && $this->getExpire() < \XLite\Core\Converter::time();
    }

    /**
     * Get expires left (seconds)
     *
     * @return integer
     */
    public function getExpiresLeft()
    {
        return $this->getExpire() - \XLite\Core\Converter::time();
    }

    /**
     * Check - has attempts limit or not
     *
     * @return boolean
     */
    public function hasAttemptsLimit()
    {
        return 0 < $this->getAttemptLimit();
    }

    /**
     * Check attaempts counter state - ended or not
     *
     * @return boolean
     */
    public function isAttemptsEnded()
    {
        return $this->hasAttemptsLimit() && 0 >= $this->getAttemptsLeft();
    }

    /**
     * Get attempts left
     *
     * @return integer
     */
    public function getAttemptsLeft()
    {
        return $this->getAttemptLimit() - $this->getAttempt();
    }

    /**
     * Inrementc attempt
     *
     * @return void
     */
    public function incrementAttempt()
    {
        $this->setAttempt($this->getAttempt() + 1);
    }

    /**
     * Get download URL
     *
     * @return string
     */
    public function getURL()
    {
        return $this->getAttachment()->getStorage()->getDownloadURL($this);
    }

    /**
     * Renew record
     *
     * @return void
     *
     * @PrePersist
     */
    public function renew()
    {
        $this->setDownloadKey(hash('sha512', strval(microtime(true))));
        $ttl = max(0, intval(\XLite\Core\Config::getInstance()->CDev->Egoods->ttl));
        $this->setExpire(0 < $ttl ? \XLite\Core\Converter::time() + $ttl * 86400 : 0);
        $this->setAttempt(0);
        $limit = max(0, intval(\XLite\Core\Config::getInstance()->CDev->Egoods->attempts_limit));
        $this->setAttemptLimit($limit * $this->getItem()->getAmount());
        $this->setBlocked(false);
    }

    // }}}
}

