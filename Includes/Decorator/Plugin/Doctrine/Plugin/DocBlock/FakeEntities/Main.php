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

namespace Includes\Decorator\Plugin\Doctrine\Plugin\DocBlock\FakeEntities;

/**
 * Main 
 *
 */
class Main extends \Includes\Decorator\Plugin\Doctrine\Plugin\DocBlock\ADocBlock
{
    /**
     * Condition to check for rewrite
     *
     * @param \Includes\Decorator\DataStructure\Graph\Classes $node Current node
     *
     * @return boolean
     */
    protected function checkRewriteCondition(\Includes\Decorator\DataStructure\Graph\Classes $node)
    {
        return parent::checkRewriteCondition($node)
            && $node->isDecorator()
            && $this->isEntityDecorator($node);
    }

    /**
     * Condition to check for rewrite
     *
     * @param \Includes\Decorator\DataStructure\Graph\Classes $node Current node
     *
     * @return boolean
     */
    protected function isEntityDecorator(\Includes\Decorator\DataStructure\Graph\Classes $node)
    {
        $result = false;

        $parent = static::getClassesTree()->find($node->getParentClass());
        while ($parent) {
            if ($parent->getTag('Entity')) {
                $result = true;
                break;
            }
            $parent = static::getClassesTree()->find($parent->getParentClass());
        }

        return $result;

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
        list($result, $flag) = parent::getTagsToAdd($node);
        $result[] = 'Entity';

        return array($result, $flag);
    }
}
