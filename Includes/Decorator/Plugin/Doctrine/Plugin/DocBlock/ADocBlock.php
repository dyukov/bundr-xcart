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

namespace Includes\Decorator\Plugin\Doctrine\Plugin\DocBlock;

/**
 * Abstract docblock plugin
 */
abstract class ADocBlock extends \Includes\Decorator\Plugin\Doctrine\Plugin\APlugin
{
    /**
     * Execute certain hook handler
     *
     * @return void
     */
    public function executeHookHandler()
    {
        static::getClassesTree()->walkThrough(array($this, 'correctTags'));
    }

    /**
     * Check and correct (if needed) class doc block comment
     *
     * @param \Includes\Decorator\DataStructure\Graph\Classes $node Current node
     *
     * @return void
     */
    public function correctTags(\Includes\Decorator\DataStructure\Graph\Classes $node)
    {
        if ($this->checkRewriteCondition($node)) {
            $this->correctTagsOnElement($node);
        }
    }

    /**
     * Correct (if needed) class doc block comment. Works for one element from the queue
     *
     * @param \Includes\Decorator\DataStructure\Graph\Classes $node Current node
     *
     * @return void
     */
    protected function correctTagsOnElement(\Includes\Decorator\DataStructure\Graph\Classes $node)
    {
        $path = LC_DIR_CACHE_CLASSES . $node->getPath();

        \Includes\Utils\FileManager::write(
            $path,
            \Includes\Decorator\Utils\Tokenizer::getSourceCode(
                $path,
                null,
                null,
                null,
                call_user_func_array(array($node, 'addLinesToDocBlock'), $this->getTagsToAdd($node)),
                $node->isDecorator() ? 'abstract' : null
            )
        );
    }

    /**
     * Condition to check for rewrite
     *
     * @param \Includes\Decorator\DataStructure\Graph\Classes $node Current node
     *
     * @return boolean
     */
    protected function checkRewriteCondition(\Includes\Decorator\DataStructure\Graph\Classes $node)
    {
        return $node->isEntity();
    }

    /**
     * Return DocBlock tags
     *
     * @param \Includes\Decorator\DataStructure\Graph\Classes $node Current node
     *
     * @return array
     */
    protected function getTagsToAdd(\Includes\Decorator\DataStructure\Graph\Classes $node)
    {
        $result = array();

        if ($node->getTag('HasLifecycleCallbacks')) {
            $result[] = 'HasLifecycleCallbacks';
        }

        return array($result, false);
    }
}
