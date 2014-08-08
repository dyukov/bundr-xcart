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

namespace XLite\View;

/**
 * File Selector Dialog widget
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class FileSelectorDialog extends \XLite\View\SimpleDialog
{
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();
        $list[] = 'select_file';

        return $list;
    }

    /**
     * Return title
     *
     * @return string
     */
    protected function getHead()
    {
        return 'Select file';
    }

    /**
     * Defines the message for uploading files
     * 
     * @return string
     */
    protected function getUploadFileMessage()
    {
        return static::t(
            'The maximum file size that can be uploaded: X',
            array('upload_max_filesize' => ini_get('upload_max_filesize'))
        );
    }

    /**
     * Return file name for the center part template
     *
     * @return string
     */
    protected function getBody()
    {
        return 'file_selector/body.tpl';
    }

    /**
     * Return parameters to use in file dialog form
     *
     * @return array
     */
    protected function getFileDialogParams()
    {
        $modelForm = $this->getModelForm();

        return array(
            // Inner name (inner identification) of object which is joined with file (product, category)
            // It identifies the model to join file with
            'object'        => $modelForm->getObject(),
            // Identificator of object joined with file
            'objectId'      => $modelForm->getObjectId(),
            // Inner name (inner identification) of file (image, attachment)
            // It identifies the model to store file in
            'fileObject'    => $modelForm->getFileObject(),
            // Identificator of file object (zero for NEW file)
            'fileObjectId'  => $modelForm->getFileObjectId(),
        );
    }
}
