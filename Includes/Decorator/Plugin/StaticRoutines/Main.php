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

namespace Includes\Decorator\Plugin\StaticRoutines;

/**
 * Main 
 *
 */
class Main extends \Includes\Decorator\Plugin\APlugin
{
    /**
     * Name of the so called "static constructor"
     */
    const STATIC_CONSTRUCTOR_METHOD = '__constructStatic';

    /**
     * Execute certain hook handler
     *
     * @return void
     */
    public function executeHookHandler()
    {
        static::getClassesTree()->walkThrough(array($this, 'addStaticConstructorCall'));
    }

    /**
     * Add static constructor calls
     * NOTE: method is public since it's used as a callback in external class
     *
     * @param \Includes\Decorator\DataStructure\Graph\Classes $node Current node
     *
     * @return void
     */
    public function addStaticConstructorCall(\Includes\Decorator\DataStructure\Graph\Classes $node)
    {
        if ($this->checkForStaticConstructor($node)) {
            $this->writeCallToSourceFile($node);
        }
    }

    /**
     * Check if node has the static constructor defined
     *
     * @param \Includes\Decorator\DataStructure\Graph\Classes $node Node to check
     *
     * @return boolean
     */
    protected function checkForStaticConstructor(\Includes\Decorator\DataStructure\Graph\Classes $node)
    {
        return $node->getReflection()->hasStaticConstructor;
    }

    /**
     * Modify class source
     *
     * @param \Includes\Decorator\DataStructure\Graph\Classes $node Current node
     *
     * @return void
     */
    protected function writeCallToSourceFile(\Includes\Decorator\DataStructure\Graph\Classes $node)
    {
        $path = LC_DIR_CACHE_CLASSES . $node->getPath();

        $content  = \Includes\Utils\FileManager::read($path);
        $content .= PHP_EOL . '// Call static constructor' . PHP_EOL;
        $content .= '\\' . $node->getClass() . '::' . static::STATIC_CONSTRUCTOR_METHOD . '();';

        \Includes\Utils\FileManager::write($path, $content);
    }
}
