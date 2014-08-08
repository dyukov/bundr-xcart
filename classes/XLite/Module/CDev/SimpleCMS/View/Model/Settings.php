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

namespace XLite\Module\CDev\SimpleCMS\View\Model;

/**
 * Settings dialog model widget
 */
abstract class Settings extends \XLite\View\Model\Settings implements \XLite\Base\IDecorator
{
    /**
     * Logo & Favicon validation flag
     *
     * @var boolean
     */
    protected $logoFaviconValidation = true;

    /**
     * Populate model object properties by the passed data
     *
     * @param array $data Data to set
     *
     * @return void
     */
    protected function setModelProperties(array $data)
    {
        $options = $this->getOptions();

        if ('CDev\SimpleCMS' == $options[0]->category) {
            foreach(array('logo', 'favicon') as $key => $imageType) {
                $data[$imageType] = $this->prepareImageData($options[$key]->value, $imageType);
            }
        }

        parent::setModelProperties($data);
    }

    /**
     * Check for the form errors
     *
     * @return boolean
     */
    public function isValid()
    {
        return parent::isValid() && $this->logoFaviconValidation;
    }

    /**
     * Additional preparations for images.
     * Upload them into specific directory
     *
     * @param string  $optionValue
     * @param string  $imageType
     *
     * @return string
     */
    protected function prepareImageData($optionValue, $imageType)
    {
        $value = $optionValue;
        $dir = static::getLogoFaviconDir();
        if (
            $_FILES
            && $_FILES[$imageType]
            && $_FILES[$imageType]['name']
        ) {
            \Includes\Utils\FileManager::deleteFile(
                $dir . LC_DS . ('favicon' === $imageType ? static::FAVICON : $_FILES[$imageType]['name'])
            );
            $path = \Includes\Utils\FileManager::moveUploadedFile(
                $imageType,
                $dir,
                'favicon' === $imageType ? static::FAVICON : null
            );
            if ($path) {
                if ($optionValue) {
                    \Includes\Utils\FileManager::deleteFile($dir . $optionValue);
                }
                $value = basename($path);
            } else {
                $this->logoFaviconValidation = false;
                \XLite\Core\TopMessage::addError('The "{{file}}" file was not uploaded', array('file' => $_FILES[$imageType]['name']));
            }
        } elseif (\XLite\Core\Request::getInstance()->useDefaultImage[$imageType]) {
            if ($optionValue) {
                \Includes\Utils\FileManager::deleteFile($dir . $optionValue);
            }
            $value = '';
        }

        return $value;
    }

    /**
     * Defines the directory where images (logo, favicon) will be stored
     *
     * @return string
     */
    protected static function getLogoFaviconDir()
    {
        return LC_DIR;
    }
}

