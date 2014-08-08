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

namespace XLite\Module\CDev\PINCodes\Controller\Admin;

/**
 * PINCodes selected controller
 *
 */
class AddPinCodes extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Add PIN codes';
    }

    /**
     * Add posted codes
     *
     * @return void
     */
    protected function doActionAdd()
    {
        $stream = fopen('data://text/plain,' . \XLite\Core\Request::getInstance()->codes, 'r');
        $this->addFromStreamAction($stream);
        if ($stream) {
            fclose($stream);
        }
    }

    /**
     * Add codes from csv file
     *
     * @return void
     */
    protected function doActionImport()
    {
        $stream = fopen(\XLite\Core\Session::getInstance()->pinCodesImportFile, 'r');
        $this->addFromStreamAction($stream);
        if ($stream) {
            fclose($stream);
        }

        $this->setReturnUrl(
            $this->buildUrl(
                'product',
                '',
                array('product_id' => \XLite\Core\Request::getInstance()->product_id, 'page' => 'pin_codes')
            )
        );
    }

    /**
     * Set sale price parameters for products list
     *
     * @return void
     */
    protected function addFromStreamAction($stream)
    {
        $product = \XLite\Core\Database::getRepo('XLite\Model\Product')->find(
            \XLite\Core\Request::getInstance()->product_id
        );

        if (!is_resource($stream)) {
            \XLite\Logger::getInstance()->log(
                'No valid resource supplied to add pin codes controller.'
                . ' Data type: ' . gettype($stream),
                LOG_ERR
            );
            \XLite\Core\TopMessage::addError('Unknown error occurred');

        } elseif (!$product) {
            \XLite\Logger::getInstance()->log(
                'No valid product id supplied to add pin codes controller.'
                . ' Request data: ' . print_r(\XLite\Core\Request::getInstance()->getData(), true),
                LOG_ERR
            );
            \XLite\Core\TopMessage::addError('Unknown error occurred');

        } else {

            $codes = array();
            $created = 0;
            $duplicates = 0;
            $exceededLength = 0;
            $maxLength = 64;

            for ($data = fgetcsv($stream); false !== $data; $data = fgetcsv($stream)) {
                $code = trim($data[0]);

                if (strlen($code) > $maxLength) {
                    $exceededLength++;
                    $code = '';
                }

                if (!empty($code)) {
                    $existing = \XLite\Core\Database::getRepo('XLite\Module\CDev\PINCodes\Model\PinCode')->findOneBy(
                        array (
                            'product' => $product->getId(),
                            'code' => $code
                        )
                    );
                    if (!$existing) {
                        $existing = in_array($code, $codes);
                    }
                    if ($existing) {
                        $duplicates++;
                    } else {
                        $object = new \XLite\Module\CDev\PINCodes\Model\PinCode;
                        $object->setCode($code);
                        $object->setProduct($product);
                        \XLite\Core\Database::getEM()->persist($object);
                        $created++;
                    }

                    $codes[] = $code;

                    if (1000 < count($codes)) {
                        \XLite\Core\Database::getEM()->flush();
                        $codes = array();
                    }
                }
            }

            \XLite\Core\Database::getEM()->flush();

            if ($created) {
                \XLite\Core\TopMessage::addInfo(static::t('X PIN codes created successfully.', array('count' => $created)));
            }
            if ($duplicates) {
                \XLite\Core\TopMessage::addWarning(static::t('X PIN code duplicates ignored.', array('count' => $duplicates)));
            }
            if ($exceededLength) {
                \XLite\Core\TopMessage::addError(
                    static::t(
                        'X PIN codes longer than Y characters ignored.',
                        array('count' => $exceededLength, 'max' => $maxLength)
                    )
                );
            }
            if (!$created && !$duplicates && !$exceededLength) {
                \XLite\Core\TopMessage::addError(static::t('No valid code found.'));
            }
        }

    }
}
