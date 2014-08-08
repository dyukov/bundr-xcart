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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Core;

/**
 * LESS parser wrapper
 */
class LessParser extends \XLite\Base\Singleton
{
    /**
     * Less parser object
     *
     * @var \Less_Parser
     */
    protected $parser;

    /**
     * Http or https
     *
     * @var mixed
     */
    protected $http = null;

    /**
     * admin or default interface.
     * If none is defined then interface will be detected via \XLite::isAdminZone() method
     *
     * @var mixed
     */
    protected $interface = null;

    /**
     * Defines the cache dir for the media type
     *
     * @param string $media Media type
     *
     * @return string
     */
    protected function getCacheDir($media)
    {
        $interface = is_null($this->interface) ? (\XLite::isAdminZone() ? 'admin' : 'default') : $this->interface;
        $http = is_null($this->http) ? (\XLite\Core\Request::getInstance()->isHTTPS() ? 'https' : 'http') : $this->http;

        return LC_DIR_CACHE_RESOURCES
            . $interface . LC_DS
            . $http . LC_DS
            . $media . LC_DS;
    }

    /**
     * Interface setter
     *
     * @param string $interface The interface which will be used for less generation. Can be 'admin', 'default'.
     *
     * @return void
     */
    public function setInterface($interface)
    {
        $this->interface = $interface;
    }

    /**
     * Http or https setter
     *
     * @param string $http Can be 'http' or 'https'
     *
     * @return void
     */
    public function setHttp($http)
    {
        $this->http = $http;
    }

    /**
     * Make a css file compiled from the LESS files collection
     *
     * @param array $lessFiles LESS files structures array
     *
     * @return array
     */
    public function makeCSS($lessFiles)
    {
        $file = $this->makeLESSResourcePath($lessFiles);
        $path = $this->getCSSResource($lessFiles);
        $url  = $this->getCSSResourceURL($path);

        $data = array(
            'file'  => $path,
            'media' => 'screen', // It is hardcoded right now
            'url'   => $url,
        );

        if ($this->needToCompileLessResource($path)) {
            try {
                $this->parser->parseFile($file, '');
                $this->parser->ModifyVars($this->getModifiedLESSVars($data));

                $content = $this->prepareLESSContent($this->parser->getCss(), $path, $data);

                \Includes\Utils\FileManager::mkdirRecursive(dirname($path));
                \Includes\Utils\FileManager::write($path, $content);

            } catch (\Exception $e) {
                \XLite\Logger::getInstance()->registerException($e);
                $data = null;
            }
        }

        return $data;
    }

    /**
     * Create a unique name for the less files collection
     *
     * @param array $lessFiles LESS files structures array
     *
     * @return string
     */
    protected function getUniquieName($lessFiles)
    {
        foreach ($lessFiles as $id => $lessFile) {
            unset($lessFiles[$id]['file']);
        }

        return hash('md4', serialize($lessFiles));
    }

    /**
     * Create a main less file for the provided less files collection
     *
     * @param array $lessFiles LESS files structures array
     *
     * @return string LESS file name
     */
    protected function makeLESSResourcePath($lessFiles)
    {
        $filePath = $this->getCacheDir('screen') . $this->getUniquieName($lessFiles) . '.less';

        if (!is_file($filePath)) {
            $content = '';

            foreach ($lessFiles as $resource) {
                $resourcePath = \Includes\Utils\FileManager::makeRelativePath($this->getCacheDir('screen'), $resource['file']);
                $content .= "\r\n" . '@import "' . str_replace('/', LC_DS, $resourcePath) . '";' . "\r\n";
            }

            \Includes\Utils\FileManager::mkdirRecursive(dirname($filePath));
            \Includes\Utils\FileManager::write($filePath, $content);
        }

        return $filePath;
    }

    /**
     * Defines the name for the CSS resource
     * CSS resource is compilation of the provided LESS files
     *
     * @param array $lessFiles LESS files structures array
     *
     * @return string
     */
    protected function getCSSResource($lessFiles)
    {
        return $this->getCacheDir('screen') . $this->getUniquieName($lessFiles) . '.css';
    }

    /**
     * Defines the URL for the CSS resource
     *
     * @param string $path File path to the CSS resource
     *
     * @return string
     */
    protected function getCSSResourceURL($path)
    {
        return \XLite::getInstance()->getShopURL(
            str_replace(LC_DS, '/', substr(dirname($path), strlen(LC_DIR_ROOT))) . '/' . basename($path)
        );
    }

    /**
     * Check if the less resource must be compiled
     *
     * @param type $data
     *
     * @return boolean
     */
    protected function needToCompileLessResource($file)
    {
        return !file_exists($file);
    }

    /**
     * Prepare LESS content
     *
     * @param string $content Content
     * @param string $path    Path
     * @param array  $data    Resource
     *
     * @return string
     */
    protected function prepareLESSContent($content, $path, array $data)
    {
        $file = $data['file'];
        $rootURL = \XLite::getInstance()->getShopURL('');

        $container = $this;

        return preg_replace_callback(
            '/url\(([^)]+)\)/Ss',
            function (array $matches) use ($container, $file, $rootURL) {
                return $container->processCSSURLHandler($matches, $file, $rootURL);
            },
            $content
        );
    }

    /**
     * Process CSS URL callback
     *
     * @param array  $matches Matches
     * @param string $file    File
     *
     * @return string
     */
    public function processCSSURLHandler(array $matches, $file, $rootURL)
    {
        $url = trim($matches[1]);
        $first = substr($url, 0, 1);

        if ('"' == $first || '\'' == $first) {
            $url = stripslashes(substr($url, 1, -1));
        }

        $url = \Includes\Utils\FileManager::makeRelativePath($file, LC_DIR_ROOT . substr($url, strlen($rootURL)));

        if (preg_match('/[\'"]/Ss', $url)) {
            $url = '"' . addslashes($url) . '"';
        }

        return 'url(' . $url . ')';
    }

    /**
     * Define the new LESS variables for the specific resource
     *
     * @param array  $data Resource data
     * @param string $type Resource type
     *
     * @return array
     */
    protected function getModifiedLESSVars($data)
    {
        $xlite = \XLite::getInstance();
        $layout = \XLite\Core\Layout::getInstance();

        return array(
            // Defines the admin skin path
            'admin-skin'    => '\'' . $xlite->getShopURL(
                dirname($layout->getResourceWebPath('body.tpl', \XLite\Core\Layout::WEB_PATH_OUTPUT_URL, \XLite::ADMIN_INTERFACE))
            ) . '\'',
            'customer-skin' => '\'' . $xlite->getShopURL(
                dirname($layout->getResourceWebPath('body.tpl', \XLite\Core\Layout::WEB_PATH_OUTPUT_URL, \XLite::CUSTOMER_INTERFACE))
            ) . '\'',
            'common-skin'   => '\'' . $xlite->getShopURL(
                dirname($layout->getResourceWebPath('ZeroClipboard.swf', \XLite\Core\Layout::WEB_PATH_OUTPUT_URL, \XLite::COMMON_INTERFACE))
            ) . '\'',
        );
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function __construct()
    {
        parent::__construct();

        $this->parser = new \Less_Parser($this->getLessParserOptions());
    }

    /**
     * Get Less_Parser options
     *
     * @return array
     */
    protected function getLessParserOptions()
    {
        return array(
            'cache_dir' => LC_DIR_DATACACHE,
            'compress'  => true,
        );
    }
}
