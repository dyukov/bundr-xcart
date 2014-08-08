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

namespace XLite\Module\CDev\Egoods\Model\Product;

/**
 * Product attchament 
 *
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 */
abstract class Attachment extends \XLite\Module\CDev\FileAttachments\Model\Product\Attachment implements \XLite\Base\IDecorator
{
    /**
     * Private attachment
     *
     * @var   boolean
     *
     * @Column (type="boolean")
     */
    protected $private = false;

    /**
     * Old scope 
     * 
     * @var   boolean
     */
    protected $oldScope;

    /**
     * Set private scope flag
     * 
     * @param boolean $private Scope flag
     *  
     * @return void
     */
    public function setPrivate($private)
    {
        if (!isset($this->oldScope)) {
            $this->oldScope = $this->private;
        }

        $this->private = intval($private);

        $this->prepareChangeScope();
    }

    /**
     * Set private flag for duplicate attachment
     * 
     * @param boolean                                                             $private Private flag
     * @param \XLite\Module\CDev\FileAttachments\Model\Product\Attachment\Storage $storage Original storage
     *  
     * @return void
     */
    public function setDuplicatePrivate($private, \XLite\Module\CDev\FileAttachments\Model\Product\Attachment\Storage $storage)
    {
        $this->getStorage()->setPath($storage->getPath());
        $this->getStorage()->setStorageType($storage->getStorageType());
        $this->private = $private;
        $this->oldScope = $private;
    }

    /**
     * Prepare change scope 
     *
     * @return void
     */
    public function prepareChangeScope()
    {
        $storage = $this->getStorage();

        if (!$storage->isURL() && isset($this->oldScope) && $this->oldScope != $this->getPrivate()) {
            $duplicates = $this->getStorage()->getDuplicates();

            if ($this->getPrivate()) {
                $storage->maskStorage();

            } else {
                $storage->unmaskStorage();
            }

            foreach ($duplicates as $duplicate) {
                if ($duplicate instanceof \XLite\Module\CDev\FileAttachments\Model\Product\Attachment\Storage) {
                    $duplicate->getAttachment()->setDuplicatePrivate($this->getPrivate(), $this->getStorage());
                }
            }

            $this->oldScope = $this->getPrivate();
        }
    }

    /**
     * Synchronize private state 
     * 
     * @return void
     *
     * @PrePersist
     */
    public function synchronizePrivateState()
    {
        if ($this->getStorage()->isPrivatePath()) {
            $this->oldScope = true;
            $this->setPrivate(true);
            $this->getStorage()->setFileName(
                substr(
                    $this->getStorage()->getFileName(),
                    0,
                    \XLite\Module\CDev\Egoods\Model\Product\Attachment\Storage::PRIVATE_SUFFIX_LENGTH * -1
                )
            );
        }
    }
}

