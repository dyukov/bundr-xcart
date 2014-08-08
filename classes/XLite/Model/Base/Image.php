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

namespace XLite\Model\Base;

/**
 * Image abstract store
 *
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 */
abstract class Image extends \XLite\Model\Base\Storage
{
    /**
     * MIME type to extenstion translation table
     *
     * @var array
     */
    protected static $types = array(
        'image/jpeg' => 'jpeg',
        'image/jpg'  => 'jpeg',
        'image/gif'  => 'gif',
        'image/xpm'  => 'xpm',
        'image/gd'   => 'gd',
        'image/gd2'  => 'gd2',
        'image/wbmp' => 'bmp',
        'image/bmp'  => 'bmp',
        'image/png'  => 'png',
    );

    /**
     * Width
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $width = 0;

    /**
     * Height
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $height = 0;

    /**
     * Image hash
     *
     * @var string
     *
     * @Column (type="fixedstring", length=32, nullable=true)
     */
    protected $hash;

    /**
     * Get image URL for customer front-end
     *
     * @return string
     */
    public function getFrontURL()
    {
        return (!$this->getRepository()->isCheckImage() || $this->checkImageHash()) ? parent::getFrontURL() : null;
    }

    /**
     * Check - image hash is equal data from DB or not
     *
     * @return boolean
     */
    public function checkImageHash()
    {
        $result = true;

        if ($this->getHash()) {
            list($path, $isTempFile) = $this->getLocalPath();

            $hash = \Includes\Utils\FileManager::getHash($path);

            if ($isTempFile) {
                \Includes\Utils\FileManager::deleteFile($path);
            }

            $result = $this->getHash() === $hash;
        }

        return $result;
    }

    /**
     * Check - image is exists in DB or not
     * TODO - remove - old method
     *
     * @return boolean
     */
    public function isExists()
    {
        return !is_null($this->getId());
    }

    /**
     * Clone
     *
     * @return \XLite\Model\AEntity
     */
    public function cloneEntity()
    {
        $newEntity = parent::cloneEntity();

        $newEntity->setPath('');
        $newEntity->loadFromURL($this->getURL(), true);

        return $newEntity;
    }

    /**
     * Update file path - change file extension taken from MIME information.
     *
     * @return boolean
     */
    protected function updatePathByMIME()
    {
        $result = parent::updatePathByMIME();

        if ($result && !$this->isURL()) {
            list($path, $isTempFile) = $this->getLocalPath();

            $newExtension = $this->getExtensionByMIME();
            $pathinfo = pathinfo($path);
            $newPath = \Includes\Utils\FileManager::getUniquePath(
                $pathinfo['dirname'],
                $pathinfo['filename'] . '.' . $newExtension
            );

            $result = rename($path, $newPath);

            if ($result) {
                $this->path = basename($newPath);
            }
        }

        return $result;
    }

    /**
     * Renew properties by path
     *
     * @param string $path Path
     *
     * @return void
     */
    protected function renewByPath($path)
    {
        $result = parent::renewByPath($path);

        if ($result) {
            $data = @getimagesize($path);

            if (is_array($data)) {

                $this->setWidth($data[0]);
                $this->setHeight($data[1]);
                $this->setMime($data['mime']);
                $hash = \Includes\Utils\FileManager::getHash($path);
                if ($hash) {
                    $this->setHash($hash);
                }

            } else {
                $result = false;
            }
        }

        return $result;
    }

    // {{{ Resized icons

    /**
     * Get resized image URL
     *
     * @param integer $width  Width limit OPTIONAL
     * @param integer $height Height limit OPTIONAL
     *
     * @return array (new width + new height + URL)
     */
    public function getResizedURL($width = null, $height = null)
    {
        $size = ($width ?: 'x') . '.' . ($height ?: 'x');
        $name = $this->getId() . '.' . $this->getExtension();
        $path = $this->getResizedPath($size, $name);

        $url = $this->getResizedPublicURL($size, $name);

        list($newWidth, $newHeight) = \XLite\Core\ImageOperator::getCroppedDimensions(
            $this->getWidth(),
            $this->getHeight(),
            $width,
            $height
        );

        if (!$this->isResizedIconAvailable($path)) {

            $result = $this->resizeIcon($newWidth, $newHeight, $path);

            if (!$result) {

                $url = $this->getURL();
            }
        }

        return array($newWidth, $newHeight, $url);
    }

    /**
     * Get resized file system path
     *
     * @param string $size Size prefix
     * @param string $name File name
     *
     * @return string
     */
    protected function getResizedPath($size, $name)
    {
        return $this->getRepository()->getFileSystemCacheRoot($size) . $name;
    }

    /**
     * Get resized file public URL
     *
     * @param string $size Size prefix
     * @param string $name File name
     *
     * @return string
     */
    protected function getResizedPublicURL($size, $name)
    {
        return \XLite::getInstance()->getShopURL(
            $this->getRepository()->getWebCacheRoot($size) . '/' . $name,
            \XLite\Core\Request::getInstance()->isHTTPS()
        );
    }

    /**
     * Check - resized icon is available or not
     *
     * @param string $path Resized image path
     *
     * @return boolean
     */
    protected function isResizedIconAvailable($path)
    {
        return \Includes\Utils\FileManager::isFile($path) && $this->getDate() < filemtime($path);
    }

    /**
     * Resize icon
     *
     * @param integer $width  Destination width
     * @param integer $height Destination height
     * @param string  $path   Write path
     *
     * @return array
     */
    protected function resizeIcon($width, $height, $path)
    {
        $operator = new \XLite\Core\ImageOperator($this);
        list($newWidth, $newHeight, $result) = $operator->resizeDown($width, $height);

        return false !== $result && \Includes\Utils\FileManager::write($path, $operator->getImage())
            ? array($newWidth, $newHeight)
            : null;
    }

    // }}}

    /**
     * Check file is amage or not
     *
     * @return boolean
     */
    public function isImage()
    {
        return true;
    }
}
