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
 * Tokenizer
 */
abstract class Tokenizer extends \Includes\Decorator\Utils\AUtils
{

    /**
     * Body cache 
     * 
     * @var   array
     */
    protected static $bodyCache = array();

    // {{{ Methods to get class-related tokens

    /**
     * Search for class declaration and return full class name
     *
     * @param string $path Repository file path
     *
     * @return string
     */
    public static function getFullClassName($path)
    {
        $result = static::getClassName($path) ?: static::getInterfaceName($path);

        if ($result) {
            $namespace = static::getNamespace($path);
            if ($namespace) {
                $result = $namespace . '\\' . $result;
            }
        }

        return $result;
    }

    /**
     * Get parent class name
     *
     * @param string $path Repository file path
     *
     * @return string
     */
    public static function getParentClassName($path)
    {
        return preg_match('/class\s+\S+\s+extends\s+(\S+)/Ss', substr(static::getContent($path), 0, 2048), $match)
            ? $match[1]
            : null;
    }

    /**
     * Return list of implemented interfaces
     *
     * @param string $path Repository file path
     * 
     * @return array
     */
    public static function getInterfaces($path)
    {
        return preg_match('/class\s+\S+\s+(?:extends\s+\S+\s+)?implements\s+([\\\\A-Za-z0-9,\s]+)/Ss', substr(static::getContent($path), 0, 2048), $match)
            ? array_map('trim', explode(',', $match[1]))
            : array();
    }

    /**
     * Check - class is final or not
     * 
     * @param string $path Repository file path
     *  
     * @return boolean
     */
    public static function isFinal($path)
    {
        return (bool)preg_match('/[\r\n]\s*final\s+class\s+\S+/Ss', substr(static::getContent($path), 0, 2048));
    }

    /**
     * Check - class is abstract or not
     *
     * @param string $path Repository file path
     *
     * @return boolean
     */
    public static function isAbstract($path)
    {
        return (bool)preg_match('/[\r\n]\s*abstract\s+class\s+\S+/Ss', substr(static::getContent($path), 0, 2048));
    }

    /**
     * Return class DocBlock
     *
     * @param string $path Repository file path
     * 
     * @return string
     */
    public static function getDockBlock($path)
    {
        return preg_match('/[\n\r](\/\*\*\s*(?:[\r\n]\s+\*[^\r\n]*)+[\r\n]\s+\*\/\s*)[\r\n](?:(abstract|final)\s+)?(?:class|interface)\s+\S+/USs', substr(static::getContent($path), 0, 2048), $match)
            ? trim($match[1])
            : null;
    }

    /**
     * Check if method is declared in class
     * 
     * @param string $path   Repository file path
     * @param string $method Method to search
     *  
     * @return boolean
     */
    public static function hasMethod($path, $method)
    {
        return (bool)preg_match(
            '/^\s*(?:(?:absract|static|public|protected|private)\s+)*function\s+' . $method . '\s*\(/Sm',
            static::getContent($path)
        );
    }

    /**
     * Get class name
     *
     * @param string $path Repository file path
     *
     * @return string
     */
    public static function getClassName($path)
    {
        return preg_match('/[\r\n]\s*(?:(?:abstract|final)\s+)?class\s+(\S+)\s+/Ss', substr(static::getContent($path), 0, 2048), $match)
            ? $match[1]
            : null;
    }

    /**
     * Get inteface name
     *
     * @param string $path Repository file path
     *
     * @return string
     */
    public static function getInterfaceName($path)
    {
        return preg_match('/[\r\n]\s*interface\s+(\S+)/Ss', substr(static::getContent($path), 0, 2048), $match)
            ? $match[1]
            : null;
    }

    /**
     * Get namespace
     *
     * @param string $path Repository file path
     *
     * @return string
     */
    public static function getNamespace($path)
    {
        return preg_match('/[\r\n]\s*namespace\s+(\S+)\s*;/Ss', substr(static::getContent($path), 0, 2048), $match)
            ? $match[1]
            : null;
    }

    // }}}

    // {{{ Methods to modify source code

    /**
     * Compose and return sourec code by tokens list
     *
     * @param string $path      Repository file path
     * @param string $namespace New namespace
     * @param string $class     New class name
     * @param string $parent    New parent class
     * @param string $dockblock New dockblock OPTIONAL
     * @param string $prefix    New prefix {abstract|final} OPTIONAL
     *
     * @return string
     */
    public static function getSourceCode($path, $namespace, $class, $parent, $dockblock = null, $prefix = null)
    {
        $body = static::getContent($path);

        // Class has been moved to a new location
        if (isset($namespace)) {
            $body = static::replaceNamespace($body, $namespace);
        }

        // Node class has been changed
        if (isset($class)) {
            $body = static::replaceClassName($body, $class);
        }

        // Parent class may be changed if class node has been "replanted" in classes tree
        if (isset($parent)) {
            $body = static::replaceParentClassName($body, $parent);
        }

        // Needed for some Doctrine plugins
        if (isset($dockblock)) {
            $body = static::replaceDockblock($body, $dockblock);
        }

        // To make abstract base classes in Decorator chains
        if (isset($prefix)) {
            $body = static::replaceClassType($body, $prefix);
        }

        if (isset(static::$bodyCache[$path])) {
            unset(static::$bodyCache[$path]);
        }

        return $body;
    }

    /**
     * Set new namespace in the tokens list
     *
     * @param string $body  File body
     * @param string $token Namespace to set
     *
     * @return void
     */
    protected static function replaceNamespace($body, $token)
    {
        return preg_replace('/([\r\n]\s*namespace\s+)\S+;/Ss', '$1' . $token . ';', $body);
    }

    /**
     * Set new class name in the tokens list
     *
     * @param string $body  File body
     * @param string $token Class name to set
     *
     * @return void
     */
    protected static function replaceClassName($body, $token)
    {
        return preg_replace('/([\r\n](?:abstract\s+|final\s+)?(?:class|interface)\s+)\S+/Ss', '$1' . $token, $body);
    }

    /**
     * Set new parent class name in the tokens list
     *
     * @param string $body  File body
     * @param string $token Class name to set
     *
     * @return void
     */
    protected static function replaceParentClassName($body, $token)
    {
        return preg_replace('/(class\s+\S+\s+extends\s+)\S+/Ss', '$1' . $token, $body);
    }

    /**
     * Set new dockblock in the tokens list
     *
     * @param string $body  File body
     * @param string $token Dockblock to set
     *
     * @return void
     */
    protected static function replaceDockblock($body, $token)
    {
        return preg_match('/\*\/\s*[\r\n](?:(?:abstract|final)\s+)?(?:class|interface)\s+\S+/Ss', $body)
            ? preg_replace(
                '/([\n\r])\/\*\*\s*(?:[\r\n]\s+\*[^\r\n]*)+[\r\n]\s+\*\/\s*([\r\n](?:(?:abstract|final)\s+)?(?:class|interface)\s+\S+)/USs',
                '$1' . trim($token) . '$2',
                $body
            )
            : preg_replace(
                '/([\r\n](?:(?:abstract|final)\s+)?(?:class|interface)\s+\S+)/USs',
                PHP_EOL . trim($token) . '$1',
                $body
            );
    }

    /**
     * Replace class type
     *
     * @param string $body  File body
     * @param string $token Class type to set
     *
     * @return void
     */
    protected static function replaceClassType($body, $token)
    {
        return preg_replace('/([\r\n])(?:(?:abstract|final)\s+)?(class\s+\S+)/Ss', '$1' . $token . ' $2', $body);
    }

    // }}}

    // {{{ Methods to modify code-related tokens

    /**
     * Add portion of code to the class source (to the end)
     *
     * @param string $path Repository file path
     * @param string $code Code to add
     *
     * @return string
     */
    public static function addCodeToClassBody($path, $code)
    {
        return preg_replace('/([\r\n])(\}\s*$)/Ss', '$1' . PHP_EOL . $code . PHP_EOL . '$2', static::getContent($path));
    }

    // }}}

    // {{{ Auxiliary methods

    /**
     * Get content 
     *
     * @param string $path Repository file path
     * 
     * @return string
     */
    protected static function getContent($path)
    {
        if (!isset(static::$bodyCache[$path])) {
            static::$bodyCache = array(
                $path => file_get_contents($path),
            );
        }

        return static::$bodyCache[$path];
    }

    // }}}
}
