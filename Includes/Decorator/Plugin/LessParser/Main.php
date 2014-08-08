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

namespace Includes\Decorator\Plugin\LessParser;

if (!defined('LC_CACHE_BUILDING_FINISH')) {
    define('LC_CACHE_BUILDING_FINISH', true);
}

/**
 * Customer LESS parser
 */
abstract class Main extends \Includes\Decorator\Plugin\APlugin
{
    /**
     * Define the LESS files structure
     *
     * @return array
     */
    protected static function getLESS($interface)
    {
        $list = \XLite\Core\Layout::getInstance()->getLESSResources($interface);

        $commonBootstrap = array(
            'file' => \XLite\Core\Layout::getInstance()->getResourceFullPath('bootstrap/css/bootstrap.less', \XLite::COMMON_INTERFACE),
            'media' => 'screen',
            'weight' => 0,
            'filelist' => array(
                'bootstrap/css/bootstrap.less',
            ),
            'interface' => \XLite::COMMON_INTERFACE,
            'original' => 'bootstrap/css/bootstrap.less',
            'url' => \XLite\Core\Layout::getInstance()->getResourceWebPath('bootstrap/css/bootstrap.less', \XLite\Core\Layout::WEB_PATH_OUTPUT_SHORT, \XLite::COMMON_INTERFACE),
            'less' => true,
        );

        $result = array($commonBootstrap);

        foreach ($list as $less) {
            $result[] = array(
                'file'          => \XLite\Core\Layout::getInstance()->getResourceFullPath($less, $interface),
                'media'         => 'screen',
                'merge'         => 'bootstrap/css/bootstrap.less',
                'filelist'      => array(
                    $less,
                ),
                'interface'     => null,
                'original'      => $less,
                'url'           => \XLite\Core\Layout::getInstance()->getResourceWebPath($less, \XLite\Core\Layout::WEB_PATH_OUTPUT_SHORT, $interface),
                'less'          => true,
            );
        }

        return $result;
    }
}
