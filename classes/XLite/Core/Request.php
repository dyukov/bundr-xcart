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

namespace XLite\Core;

/**
 * Request
 */
class Request extends \XLite\Base\Singleton
{
    /**
     * Current method
     */
    const METHOD_CLI = 'cli';

    /**
     * Cureent request method
     *
     * @var string
     */
    protected $requestMethod = null;

    /**
     * Request data
     *
     * @var array
     */
    protected $data = array();

    /**
     * Cache for mobile device flag
     *
     * @var null|boolean
     */
    protected static $isMobileDeviceFlag = null;

    /**
     * Detect Mobile version
     *
     * @return boolean
     */
    public static function isMobileDevice()
    {
        if (is_null(static::$isMobileDeviceFlag)) {
            static::$isMobileDeviceFlag = static::detectMobileDevice();
        }

        return static::$isMobileDeviceFlag;
    }

    /**
     * Detect if browser is mobile device
     *
     * @return boolean
     */
    public static function detectMobileDevice()
    {
        return \XLite\Core\MobileDetect::getInstance()->isMobile();
    }

    /**
     * Map request data
     *
     * @param array $data Custom data OPTIONAL
     *
     * @return void
     */
    public function mapRequest(array $data = array())
    {
        if (empty($data)) {
            if ($this->isCLI()) {
                for ($i = 1; count($_SERVER['argv']) > $i; $i++) {
                    $pair = explode('=', $_SERVER['argv'][$i], 2);
                    $data[preg_replace('/^-+/Ss', '', $pair[0])] = isset($pair[1]) ? trim($pair[1]) : true;
                }

            } else {
                $data = array_merge($this->getGetData(false), $this->getPostData(false), $this->getCookieData(false));
            }
        }

        $this->data = array_replace_recursive($this->data, $this->prepare($data));
    }

    /**
     * Return all data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return data from the $_GET global variable
     *
     * @param boolean $prepare Flag OPTIONAL
     *
     * @return array
     */
    public function getGetData($prepare = true)
    {
        return $prepare ? $this->prepare($_GET) : $_GET;
    }

    /**
     * Return data from the $_POST global variable
     *
     * @param boolean $prepare Flag OPTIONAL
     *
     * @return array
     */
    public function getPostData($prepare = true)
    {
        return $prepare ? $this->prepare($_POST) : $_POST;
    }

    /**
     * Return data from the $_COOKIE global variable
     *
     * @param boolean $prepare Flag OPTIONAL
     *
     * @return array
     */
    public function getCookieData($prepare = true)
    {
        return $prepare ? $this->prepare($_COOKIE) : $_COOKIE;
    }

    /**
     * Return data from the $_SERVER global variable
     *
     * @param boolean $prepare Flag OPTIONAL
     *
     * @return array
     */
    public function getServerData($prepare = true)
    {
        return $prepare ? $this->prepare($_SERVER) : $_SERVER;
    }

    /**
     * Return current request method
     *
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * Set request method
     *
     * @param string $method New request method
     *
     * @return void
     */
    public function setRequestMethod($method)
    {
        $this->requestMethod = $method;
    }

    /**
     * Check if current request method is "GET"
     *
     * @return boolean
     */
    public function isGet()
    {
        return 'GET' === $this->requestMethod;
    }

    /**
     * Check if current request method is "POST"
     *
     * @return boolean
     */
    public function isPost()
    {
        return 'POST' === $this->requestMethod;
    }

    /**
     * Check if current request method is "HEAD"
     *
     * @return boolean
     */
    public function isHead()
    {
        return 'HEAD' === $this->requestMethod;
    }

    /**
     * Check - is AJAX request or not
     *
     * @return boolean
     */
    public function isAJAX()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }

    /**
     * Check for secure connection
     *
     * @return boolean
     */
    public function isHTTPS()
    {
        return \XLite\Core\URLManager::isHTTPS();
    }

    /**
     * Check - is command line interface or not
     *
     * @return boolean
     */
    public function isCLI()
    {
        return 'cli' == PHP_SAPI;
    }

    /**
     * Getter
     *
     * @param string $name Property name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * Setter
     *
     * @param string $name  Property name
     * @param mixed  $value Property value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $this->prepare($value);
    }

    /**
     * Check property accessability
     *
     * @param string $name Property name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }


    /**
     * Constructor
     *
     * @return void
     */
    protected function __construct()
    {
        $this->requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : self::METHOD_CLI;
        $this->mapRequest();
    }

    /**
     * Unescape single value
     *
     * @param string $value Value to sanitize
     *
     * @return string
     */
    protected function doUnescapeSingle($value)
    {
        return stripslashes($value);
    }

    /**
     * Remove automatically added escaping
     *
     * @param mixed $data Data to sanitize
     *
     * @return mixed
     */
    protected function doUnescape($data)
    {
        return is_array($data)
            ? array_map(array($this, __FUNCTION__), $data)
            : $this->doUnescapeSingle($data);
    }

    /**
     * Normalize request data
     *
     * @param mixed $request Request data
     *
     * @return mixed
     */
    protected function normalizeRequestData($request)
    {
        if (ini_get('magic_quotes_gpc')) {
            $request = $this->doUnescape($request);
        }

        return $request;
    }

    /**
     * Wrapper for sanitize()
     *
     * @param mixed $data Data to sanitize
     *
     * @return mixed
     */
    protected function prepare($data)
    {
        if (is_array($data)) {
            if (isset($data['target']) && !$this->checkControlArgument($data['target'], 'Target')) {
                $data['target'] = \XLite::TARGET_404;
                $data['action'] = null;
            }

            if (isset($data['action']) && !$this->checkControlArgument($data['action'], 'Action')) {
                unset($data['action']);
            }
        }

        return $this->normalizeRequestData($data);
    }

    /**
     * Check control argument (like target)
     *
     * @param mixed  $value Argument value
     * @param string $name  Argument name
     *
     * @return boolean
     */
    protected function checkControlArgument($value, $name)
    {
        $result = true;

        if (!is_string($value)) {
            \XLite\Logger::getInstance()->log($name . ' has a wrong type');
            $result = false;

        } elseif (!preg_match('/^[a-z0-9_]*$/Ssi', $value)) {
            \XLite\Logger::getInstance()->log($name . ' has a wrong format');
            $result = false;
        }

        return $result;
    }
}
