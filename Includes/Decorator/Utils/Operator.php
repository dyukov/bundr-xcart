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

namespace Includes\Decorator\Utils;

/**
 * Operator
 *
 */
abstract class Operator extends \Includes\Decorator\Utils\AUtils
{
    /**
     * Suffix for a base class in decorator chain
     */
    const BASE_CLASS_SUFFIX = 'Abstract';

    protected static $classesDirLength = 0;
    
    /**
     * Tags to ignore
     *
     * @var array
     */
    protected static $ignoredTags = array('see', 'since');

    // {{ Classes tree

    /**
     * Parse all PHP class files and create the graph
     *
     * @return \Includes\Decorator\DataStructure\Graph\Classes
     */
    public static function createClassesTree()
    {
        // Tree is not a separate data structure - it's only the root node
        $root = new \Includes\Decorator\DataStructure\Graph\Classes();

        // It's the (<class_name, descriptor>) list
        foreach (($index = static::getClassesTreeIndex()) as $node) {

            // Three possibilities:
            // 1. Class has no parent. Add class to the root
            // 2. Class parent is not in the list. Add class to the root
            // 3. Class parent defined and exists in the list. Add class to that parent
            if (($class = $node->getParentClass()) && isset($index[$class])) {

                // Existing non-root parent
                $parent = $index[$class];

                // Decorator restriction (only for original classes repository)
                // DO NOT use the "===" in the second part
                if ($parent->isDecorator() && static::STEP_FIRST == static::$step) {
                    $parent->handleError('It\'s not allowed to extend a decorator', $node);
                }

                // Cases 2 & 3
                $parent->addChild($node);

            } else {

                // Case 1
                $root->addChild($node);
            }

            $root->addToIndex($node);
        }

        // Check classes tree integrity
        $root->checkIntegrity();

        return $root;
    }

    /**
     * The classes files are sorted before putting it to cache
     * 
     * @return array
     */
    protected static function getClassesDirFiles()
    {
        $files = array();
        foreach (static::getClassFileIterator()->getIterator() as $data) {
            $files[] = $data;
        }        
        static::$classesDirLength = strlen(static::getClassesDir());
        uasort($files, array('static', 'compareClassesFiles'));

        return $files;
    }

    /**
     * Compare method is used in the static::getClassesDirFiles()
     * 
     * @param \SplFileInfo $x
     * @param \SplFileInfo $y
     * 
     * @return integer
     */
    public static function compareClassesFiles(\SplFileInfo $x, \SplFileInfo $y)
    {
        return strcmp(
            substr($x->getRealPath(), static::$classesDirLength),
            substr($y->getRealPath(), static::$classesDirLength)
        );
    }

    /**
     * Parse PHP files and return plain array with the class descriptors
     *
     * @return array
     */
    protected static function getClassesTreeIndex()
    {
        $index = array();

        // Iterate over all directories with PHP class files
        foreach (static::getClassesDirFiles() as $data) {
            // Use PHP Tokenizer to search class declaration
            if (
                ($class = \Includes\Decorator\Utils\Tokenizer::getFullClassName($data->getPathname()))
                && \Includes\Utils\Operator::checkIfLCClass($class)
            ) {
                // File contains a class declaration: create node (descriptor)
                $node = new \Includes\Decorator\DataStructure\Graph\Classes($class);

                // Check parent class (so called optional dependencies for modules)
                $dependencies = $node->getTag('lc_dependencies', true);

                if (empty($dependencies) || \Includes\Utils\ModulesManager::areActiveModules($dependencies)) {

                    // Node is valid: add to the index
                    $index[$class] = $node;

                } else {
                    // The unused class file must be removed from the cache file structure
                    \Includes\Utils\FileManager::deleteFile($node->getFile());
                }
            }
        }

        return $index;
    }

    /**
     * Get iterator for class files
     *
     * @return \Includes\Utils\FileFilter
     */
    protected static function getClassFileIterator()
    {
        return new \Includes\Utils\FileFilter(
            static::getClassesDir(),
            \Includes\Utils\ModulesManager::getPathPatternForPHP()
        );
    }

    // }}}

    // {{{ Modules graph

    /**
     * Check all module dependencies and create the graph
     *
     * @return \Includes\Decorator\DataStructure\Graph\Modules
     */
    public static function createModulesGraph()
    {
        // Tree is not a separate data structure - it's only the root node
        $root = new \Includes\Decorator\DataStructure\Graph\Modules();

        // It's the (<module_name, descriptor>) list
        foreach (($index = static::getModulesGraphIndex()) as $node) {

            // Two possibilities:
            // 1. Module have dependencies. Add module as a child to all its parents
            // 2. Module have no dependencies. Add it as a child to the root node
            if ($dependencies = $node->getDependencies()) {

                // It's the (<module_name>) list
                foreach ($dependencies as $module) {

                    // Module from the dependencies may be disbaled,
                    // or included into the mutual modules list
                    // of some other module(s)
                    if (isset($index[$module])) {

                        // Case 1 (with dependencies)
                        $index[$module]->addChild($node);
                    }
                }

            } else {

                // Case 2 (without dependencies)
                $root->addChild($node);
            }
        }

        // Check modules graph integrity
        $root->checkIntegrity();

        return $root;
    }

    /**
     * Get all active modules and return plain array with the module descriptors
     *
     * @return array
     */
    protected static function getModulesGraphIndex()
    {
        $index = array();

        // Fetch all active modules from database.
        // Dependencies are checked and corrected by the ModulesManager
        foreach (\Includes\Utils\ModulesManager::getActiveModules() as $module => $tmp) {

            // Unconditionally add module to the index (since its dependencies are already checked)
            $index[$module] = new \Includes\Decorator\DataStructure\Graph\Modules($module);
        }

        return $index;
    }

    // }}}

    // {{{ Decorator routines

    /**
     * Main decorator callback: build class decoration chains
     *
     * @param \Includes\Decorator\DataStructure\Graph\Classes $node Current node
     *
     * @return void
     */
    public static function decorateClass(\Includes\Decorator\DataStructure\Graph\Classes $node)
    {
        // Two kind of node parents: which implement the
        // \XLite\Base\IDecorator interface, and regular ones
        list($decorators, $regular) = static::divideChildrenIntoGroups($node);

        // Do not perform any actions for classes which have no decorators
        if (!empty($decorators)) {

            // We do not need to re-plant first decorator:
            // it's already derived from current node
            $parent = array_shift($decorators);

            // Start from second decorator
            foreach ($decorators as $child) {

                // Move node to the decorator chain
                $child->replant($node, $parent);

                // Next step: set new top node in decorator chain
                $parent = $child;
            }

            // Save value
            $baseClass = $node->getClass();

            // Rename base class to avoid coflicts with the top-level node
            $node->setKey($node->getClass() . static::BASE_CLASS_SUFFIX, true);
            $node->setLowLevelNodeFlag();

            // Special top-level node: stub class with empty body
            $topNode = new \Includes\Decorator\DataStructure\Graph\Classes($baseClass);
            $topNode->setTopLevelNodeFlag();

            // Add this stub node as a child to the last decorator in the chain
            $parent->addChild($topNode);

            static::getClassesTree()->addToIndex($topNode);
            static::getClassesTree()->addToIndex($node);

            // Regular children must derive the top-level class in chain
            foreach ($regular as $child) {
                $child->replant($node, $topNode);
            }
        }
    }

    /**
     * Get decorators and regular children; order the first group
     *
     * @param \Includes\Decorator\DataStructure\Graph\Classes $node Current node
     *
     * @return array
     */
    protected static function divideChildrenIntoGroups(\Includes\Decorator\DataStructure\Graph\Classes $node)
    {
        // First element - list of decorators, second one - regular children.
        // Both of them may be empty
        $result = array(array(), array());

        foreach ($node->getChildren() as $child) {
            // One in the pair: (<decorators>, ...) or (..., <regular>)
            $result[$child->isDecorator() ? 0 : 1][] = $child;
        }

        list($nodeids, $edges) = static::getTopology($result[0]);

        $ids = array_flip($nodeids);
        $nodeids = static::sortTopological($nodeids, $edges);
        $tmp = $result[0];
        $result[0] = array();
        foreach ($nodeids as $className) {
            $result[0][] = $tmp[$ids[$className]];
            unset($tmp[$ids[$className]]);
        }

        if ($tmp) {
            $result[0] = array_merge($result[0], $tmp);
        }

        return $result;
    }

    /**
     * Get topology 
     * 
     * @param array $nodes Nodes
     *  
     * @return array
     */
    protected static function getTopology(array $nodes)
    {
        $nodeids = array();
        $edges = array();
        foreach ($nodes as $node) {
            $nodeids[] = $node->getClass();
            $dependencies = $node->getTag('lc_dependencies', true);
            if ($dependencies) {
                foreach ($dependencies as $depend) {
                    foreach ($nodes as $node2) {
                        $module = $node2->getModuleName();
                        if ($module && $module == $depend && $node2->getClass() != $node->getClass()) {
                            $edges[] = array(
                                $node2->getClass(),
                                $node->getClass(),
                            );
                        }
                    }
                }
            }
        }

        return array($nodeids, $edges);
    }

    /**
     * Sort topological 
     * 
     * @param array $nodeids Node ID list
     * @param array $edges   Topology edges
     *  
     * @return array
     */
    protected static function sortTopological(array $nodeids, array $edges)
    {
        // initialize variables
        $L = $S = $nodes = array(); 

        // remove duplicate nodes
        $nodeids = array_unique($nodeids);  

        // remove duplicate edges
        $hashes = array();
        foreach ($edges as $k => $e) {
            $hash = md5(serialize($e));
            if (in_array($hash, $hashes)) {
                unset($edges[$k]);

            } else {
                $hashes[] = $hash;
            }
        }

        // Build a lookup table of each node's edges
        foreach ($nodeids as $id) {
            $nodes[$id] = array(
                'in'  => array(),
                'out' => array(),
            );
            foreach ($edges as $e) {
                if ($id == $e[0]) {
                    $nodes[$id]['out'][] = $e[1];
                }
                if ($id == $e[1]) {
                    $nodes[$id]['in'][] = $e[0];
                }
            }
        }

        // While we have nodes left, we pick a node with no inbound edges, 
        // remove it and its edges from the graph, and add it to the end 
        // of the sorted list.
        foreach ($nodes as $id => $n) {
            if (empty($n['in'])) {
                $S[] = $id;
            }
        }

        while (!empty($S)) {
            $L[] = $id = array_shift($S);
            foreach ($nodes[$id]['out'] as $m) {
                $nodes[$m]['in'] = array_diff($nodes[$m]['in'], array($id));
                if (empty($nodes[$m]['in'])) {
                    $S[] = $m;
                }
            }
            $nodes[$id]['out'] = array();
        }

        // Check if we have any edges left unprocessed
        foreach ($nodes as $n) {
            if (!empty($n['in']) || !empty($n['out'])) {
                $L = null; // not sortable as graph is cyclic
                break;
            }
        }
        return $L;
    }

    // }}}

    // {{{ Cache writing

    /**
     * Write PHP class to the files
     *
     * @param \Includes\Decorator\DataStructure\Graph\Classes $node   Current class node
     * @param \Includes\Decorator\DataStructure\Graph\Classes $parent Parent class node
     *
     * @return void
     */
    public static function writeClassFile(
        \Includes\Decorator\DataStructure\Graph\Classes $node,
        \Includes\Decorator\DataStructure\Graph\Classes $parent = null
    ) {
        \Includes\Utils\FileManager::write(LC_DIR_CACHE_CLASSES . $node->getPath(), $node->getSource($parent));
    }

    // }}}

    // {{{ Tags parsing

    /**
     * Parse dockblock to get tags
     *
     * @param string $content String to parse
     * @param array  $tags    Tags to search OPTIONAL
     *
     * @return array
     */
    public static function getTags($content, array $tags = array())
    {
        $result = array();

        if (preg_match_all(static::getTagPattern($tags), $content, $matches)) {
            $tags = static::parseTags($matches);

            if (!empty($tags)) {
                $result += static::parseTags($matches);
            }
        }

        return $result;
    }

    /**
     * Return pattern to parse source for tags
     *
     * @param array $tags List of tags to search
     *
     * @return string
     */
    public static function getTagPattern(array $tags)
    {
        return '/@\s*(' . (empty($tags) ? '\w+' : implode('|', $tags)) . ')(?=\s*)([^@\n]*)?/Smi';
    }

    /**
     * Parse dockblock to get tags
     *
     * @param array $matches Data from preg_match_all()
     *
     * @return array
     */
    protected static function parseTags(array $matches)
    {
        $result = array(array(), array());

        // Sanitize data
        array_walk($matches[2], function (&$value) { $value = trim(trim($value), ')('); });

        // There are so called "multiple" tags
        foreach (array_unique($matches[1]) as $tag) {

            // Ignore some time to save memory and time
            if (in_array($tag, static::$ignoredTags)) continue;

            // Check if tag is defined only once
            if (1 < count($keys = array_keys($matches[1], $tag))) {
                $list = array();

                // Convert such tag values into the single array
                foreach ($keys as $key) {

                    // Parse list of tag attributes and their values
                    $list[] = static::parseTagValue($matches[2][$key]);
                }

                // Add tag name and its values to the end of tags list.
                // All existing entries for this tag was cleared by the "unset()"
                $result[0][] = $tag;
                $result[1][] = $list;

            // If the value was parsed (the corresponded tokens were found), change its type to the "array"
            } elseif ($matches[2][$key = array_shift($keys)] !== ($value = static::parseTagValue($matches[2][$key]))) {

                $result[0][] = $tag;
                $result[1][] = array($value ?: $matches[2][$key]);
            }
        }

        // Create an associative array of tag names and their values
        return !empty($result[0]) && !empty($result[1])
            ? array_combine(array_map('strtolower', $result[0]), $result[1])
            : array();
    }

    /**
     * Parse value of a phpDocumenter tag
     *
     * @param string $value Value to parse
     *
     * @return array
     */
    protected static function parseTagValue($value)
    {
        return \Includes\Utils\Converter::parseQuery($value, '=', ',', '"\'');
    }

    // }}}
}
