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

namespace XLite\Module\CDev\Egoods\Model\Product\Attachment;

/**
 * Storage
 *
 * @MappedSuperclass
 */
abstract class Storage extends \XLite\Module\CDev\FileAttachments\Model\Product\Attachment\Storage implements \XLite\Base\IDecorator
{
    /**
     * Private suffix length
     */
    const PRIVATE_SUFFIX_LENGTH = 33;

    /**
     * Get URL
     *
     * @return string
     */
    public function getURL()
    {
        return $this->getAttachment()->getPrivate() ? $this->getGetterURL() : parent::getURL();
    }

    /**
     * Get URL for customer front-end
     *
     * @return string
     */
    public function getFrontURL()
    {
        return $this->getAttachment()->getPrivate() ? null : parent::getFrontURL();
    }

    /**
     * Get file extension
     *
     * @return string
     */
    public function getExtension()
    {
        $ext = null;
        if ($this->getAttachment()->getPrivate() && !$this->isURL()) {
            $ext = explode('.', pathinfo($this->getPath(), PATHINFO_FILENAME));
            $ext = $ext[count($ext) - 1];
        }

        return $ext ?: parent::getExtension();
    }

    /**
     * Get download URL for customer front-end by key
     *
     * @return string
     */
    public function getDownloadURL(\XLite\Module\CDev\Egoods\Model\OrderItem\PrivateAttachment $attachment)
    {
        $params = $this->getGetterParams();
        $params['key'] = $attachment->getDownloadKey();

        return \XLite\Core\Converter::buildFullURL('storage', 'download', $params, \XLite::CART_SELF);
    }

    /**
     * Mask storage
     *
     * @return void
     */
    public function maskStorage()
    {
        $path = $this->getStoragePath();
        $suffix = md5(strval(microtime(true)) . strval(rand(0, 1000000)));
        rename($path, $path . '.' . $suffix);
        $this->setPath($this->getPath() . '.' . $suffix);
    }

    /**
     * Unmask storage
     *
     * @return void
     */
    public function unmaskStorage()
    {
        $path = $this->getStoragePath();
        rename($path, substr($path, 0, static::PRIVATE_SUFFIX_LENGTH * -1));
        $this->setPath(substr($this->getPath(), 0, static::PRIVATE_SUFFIX_LENGTH * -1));
    }

    /**
     * Check - path ir private or not
     *
     * @param string $path Path OPTIONAL
     *
     * @return boolean
     */
    public function isPrivatePath($path = null)
    {
        $path = $path ?: $this->getPath();

        return (bool)preg_match('/\.[a-f0-9]{32}$/Ss', $path);
    }
}

