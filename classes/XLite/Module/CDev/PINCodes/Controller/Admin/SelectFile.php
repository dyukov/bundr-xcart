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
 * Select File controller
 *
 */
class SelectFile extends \XLite\Controller\Admin\SelectFile implements \XLite\Base\IDecorator
{
    /**
     * Get redirect target 
     * 
     * @return string
     */
    protected function getRedirectTarget()
    {
        $target = parent::getRedirectTarget();

        if ('import_pin_codes' == $target) {
            $target = 'add_pin_codes';
        }

        return $target;
    }

    /**
     * Return parameters array for "Import" target
     *
     * @return string
     */
    protected function getParamsObjectImportPinCodes()
    {
        return array(
            'action' => 'import',
            'product_id' => \XLite\Core\Request::getInstance()->objectId
        );
    }

    /**
     * Common handler for pin codes import
     *
     * @param string $methodToLoad Method to use for getting file
     * @param array  $paramsToLoad Parameters to use in getter method
     *
     * @return void
     */
    protected function doActionSelectImportPinCodes($methodToLoad, array $paramsToLoad)
    {
        \XLite\Core\Session::getInstance()->importPinCodesCell = null;
        $methodToLoad .= 'Import';

        $path = call_user_func_array(array($this, $methodToLoad), $paramsToLoad);
        if (is_array($path)) {

            if (!$path[0] && $path[1]) {
                \XLite\Core\TopMessage::addError($path[1]);
            }

            $path = $path[0];
        }

        if ($path) {
            chmod($path, 0644);
            \XLite\Core\Session::getInstance()->pinCodesImportFile = $path;
        }
    }

    /**
     * "Upload" handler for pin codes import
     *
     * @return void
     */
    protected function doActionSelectUploadImportPinCodes()
    {
        $this->doActionSelectImportPinCodes('loadFromRequest', array('uploaded_file'));
    }

    /**
     * "URL" handler for import
     *
     * @return void
     */
    protected function doActionSelectUrlImportPinCodes()
    {
        $this->doActionSelectImportPinCodes(
            'loadFromURL',
            array(
                \XLite\Core\Request::getInstance()->url,
            )
        );
    }

    /**
     * "Local file" handler for import
     *
     * @return void
     */
    protected function doActionSelectLocalImportPinCodes()
    {
        $file = \XLite\View\BrowseServer::getNormalizedPath(\XLite\Core\Request::getInstance()->local_server_file);

        $this->doActionSelectImportPinCodes(
            'loadFromLocalFile',
            array($file)
        );
    }

    // }}}
}
