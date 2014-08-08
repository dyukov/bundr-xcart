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

namespace XLite\View\Model\FileDialog;

/**
 * File select dialog model widget
 */
class Select extends \XLite\View\Model\AModel
{

    /**
     * Return object name
     *
     * @return string
     */
    public function getObject()
    {
        return \XLite\Core\Request::getInstance()->object;
    }

    /**
     * Return object identificator
     *
     * @return string
     */
    public function getObjectId()
    {
        return \XLite\Core\Request::getInstance()->objectId;
    }

    /**
     * Return file object name
     *
     * @return string
     */
    public function getFileObject()
    {
        return \XLite\Core\Request::getInstance()->fileObject;
    }

    /**
     * Return file object identificator
     *
     * @return string
     */
    public function getFileObjectId()
    {
        return \XLite\Core\Request::getInstance()->fileObjectId;
    }

    /**
     * This object will be used if another one is not pased
     *
     * @return \XLite\Model\Profile
     */
    protected function getDefaultModelObject()
    {
        return null;
    }

    /**
     * Return name of web form widget class
     *
     * @return string
     */
    protected function getFormClass()
    {
        return '\XLite\View\Form\FileDialog\Select';
    }
}
