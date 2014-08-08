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

namespace XLite\View\Console;

/**
 * Console base widget
 *
 * @ListChild (list="cli.center", zone="console")
 */
class Main extends \XLite\View\Console\AConsole
{
    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();

        $result[] = 'main';

        return $result;
    }

    /**
     * Get allowed commands
     *
     * @return array
     */
    public function getAllowedCommands()
    {
        $dsQuoted = preg_quote(LC_DS, '/');
        $path = LC_DIR_CACHE_CLASSES . 'XLite' . LC_DS . 'Controller' . LC_DS . 'Console' . LC_DS . '*.php';
        $commands = array();
        foreach (glob($path) as $f) {
            if (!preg_match('/Abstract.php$/Ss', $f) && !preg_match('/' . $dsQuoted . 'A[A-Z]/Ss', $f)) {
                $commands[] = strtolower(substr(basename($f), 0, -4));
            }
        }

        return $commands;
    }


    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'base.tpl';
    }
}
