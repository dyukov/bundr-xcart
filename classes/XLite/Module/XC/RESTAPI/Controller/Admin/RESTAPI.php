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

namespace XLite\Module\XC\RESTAPI\Controller\Admin;

/**
 * REST API controller
 */
class RESTAPI extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Allowed REST methods 
     * 
     * @var   array
     */
    protected $allowedMethods = array('GET', 'POST', 'PUT', 'DELETE');

    /**
     * Path data
     *
     * @var   \ArrayObject
     */
    protected $path;

    /**
     * Handles the request.
     * Parses the request variables if necessary. Attempts to call the specified action function
     *
     * @return void
     */
    public function handleRequest()
    {
        $this->processPut();
        $this->set('silent', true);

        parent::handleRequest();
    }

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    public function checkAccess()
    {
        return parent::checkAccess()
            && \XLite\Core\Request::getInstance()->_path
            && $this->getPath()->repository
            && $this->isRESTRequestAllowed();
    }

    /**
     * Process request
     *
     * @return void
     */
    public function processRequest()
    {
    }

    /**
     * Mark controller run thread as access denied
     *
     * @return void
     */
    protected function markAsAccessDenied()
    {
        if ($this->getPath()->repository) {
            header('HTTP/1.0 403 Forbidden', true, 403);

        } else {
            header('HTTP/1.0 404 Not Found', true, 404);
        }

        $this->setSuppressOutput(true);
        $this->silence = true;
    }

    /**
     * Process PUT request
     * 
     * @return void
     */
    protected function processPut()
    {
        if ('PUT' == $_SERVER['REQUEST_METHOD']) {
            $str = '';
            $s = fopen('php://input', 'r');
            while ($kb = fread($s, 1024)) {
                $str .= $kb;
            }
            fclose($s);

            $arr = array();
            parse_str($str, $arr);

            if ($arr) {
                \XLite\Core\Request::getInstance()->mapRequest($arr);
            }
        }
    }

    /**
     * Check - REST request is allowed or not
     * 
     * @return boolean
     */
    protected function isRESTRequestAllowed()
    {
        return \XLite\Core\Config::getInstance()->XC->RESTAPI->key
            && \XLite\Core\Config::getInstance()->XC->RESTAPI->key == \XLite\Core\Request::getInstance()->_key;
    }

    /**
     * Check - is current place public or not
     *
     * @return boolean
     */
    protected function isPublicZone()
    {
        return true;
    }

    /**
     * Preprocessor for no-action run
     *
     * @return void
     */
    protected function doNoAction()
    {
        try {
            $data = $this->{'process' . ucfirst($this->getPath()->method) . 'RESTRequest'}();
            $data = $this->getPath()->repository->processRESTRequest($this->getPath()->method, $data);

        } catch (\Exception $e) {
            header('HTTP/1.0 400 Bad Request', true, 400);
            header('X-REST-Error: ' . $e->getMessage());
            \XLite\Logger::getInstance()->registerException($e);
            $data = null;
        }

        $this->printRESTRequest($data);
    }

    /**
     * Call postprocess method
     *
     * @param string $method Method name
     *
     * @return void
     */
    protected function callPostprocessMethod($method)
    {
        $method = 'postprocess' . ucfirst($method) . 'RESTRequest';
        if (method_exists($this->getPath()->repository, $method)) {
            $this->getPath()->repository->{$method}();
        }
    }

    // {{{ Common routines

    /**
     * Get method 
     * 
     * @return string
     */
    protected function getMethod()
    {
        if (!empty(\XLite\Core\Request::getInstance()->_method)) {
            $method = strtoupper(\XLite\Core\Request::getInstance()->_method);

        } elseif (!empty($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);

        } else {
            $method = strtoupper(\XLite\Core\Request::getInstance()->getRequestMethod());
        }

        if (!in_array($method, $this->allowedMethods)) {
            $method = $this->allowedMethods[0];
        }

        return $method;
    }

    /**
     * Get path 
     * 
     * @return array
     */
    protected function getPath()
    {
        if (!isset($this->path)) {
            $this->path = $this->definePath();
        }

        return $this->path;
    }

    /**
     * Define path
     *
     * @return \ArrayObject
     */
    protected function definePath()
    {
        $parts = explode('/', \XLite\Core\Request::getInstance()->_path);
        $one = isset($parts[1]);

        return new \ArrayObject(
            array(
                'method'     => strtolower($this->getMethod()) . ($one ? 'One' : 'All'),
                'multiple'   => !$one,
                'class'      => $this->getEntityClass($parts[0]),
                'repository' => $this->getRepository($parts[0]),
                'id'         => isset($parts[1]) ? trim($parts[1]) : null,
            ),
            \ArrayObject::ARRAY_AS_PROPS
        );
    }

    /**
     * Get entity class
     * 
     * @param string $path Path
     *  
     * @return string
     */
    protected function getEntityClass($path)
    {
        $parts = array_map('ucfirst', explode('-', strtolower($path)));

        $path = $this->normalizeFilePath('XLite' . LC_DS . 'Model' . LC_DS . implode(LC_DS, $parts) . '.php');
        $class = $path ? str_replace(LC_DS, '\\', substr($path, 0, -4)) : null;
        if (!$path || !\XLite\Core\Operator::isClassExists($class)) {
            $class = null;
        }

        if (!$class && 2 < count($parts)) {
            $module = \XLite\Core\Database::getRepo('XLite\Model\Module')->findOneBy(array('author' => $parts[0], 'name' => $parts[1]));
            if ($module && $module->getEnabled()) {
                $path = $this->normalizeFilePath(
                    'XLite' . LC_DS . 'Module' . LC_DS
                    . $parts[0] . LC_DS . $parts[1] . LC_DS . 'Model'
                    . LC_DS . implode(LC_DS, array_slice($parts, 2)) . '.php'
                );
                $class = $path ? str_replace(LC_DS, '\\', substr($path, 0, -4)) : null;
                if (!$path || !\XLite\Core\Operator::isClassExists($class)) {
                    $class = null;
                }
            }
        }

        return $class;
    }

    /**
     * Get repository
     *
     * @param string $path Path
     *
     * @return \XLite\Model\Repo\ARepo
     */
    protected function getRepository($path)
    {
        $class = $this->getEntityClass($path);

        return $class ? \XLite\Core\Database::getRepo($class) : null;
    }

    /**
     * Normalize file path 
     * 
     * @param string $relPath Relative file path
     *  
     * @return string
     */
    protected function normalizeFilePath($relPath)
    {
        $baseDir = LC_DIR_CACHE_CLASSES;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir),
            \RecursiveIteratorIterator::LEAVES_ONLY,
            \FilesystemIterator::SKIP_DOTS
        );

        $baseLength = strlen($baseDir);
        $lowPath = strtolower($relPath);
        $result = null;
        foreach ($iterator as $path => $file) {
            $path = substr($path, $baseLength);
            if (strtolower($path) == $lowPath) {
                $result = $path;
                break;
            }
        }

        return $result;
    }

    // }}}

    // {{{ Input

    /**
     * Get input 
     * 
     * @return array
     */
    protected function getInput()
    {
        $data = null;

        $request = \XLite\Core\Request::getInstance();

        if (!empty($request->model)) {
            if (is_string($request->model)) {
                $data = json_decode($request->model, true);
                if (!is_array($data)) {
                    $data = null;
                }

            } elseif (is_array($request->model)) {
                $data = $request->model;
            }

        } else {
            $data = $request->getData();
            foreach ($this->getServiceInputKeys() as $key) {
                if (isset($data[$key])) {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }

    /**
     * Get service input keys 
     * 
     * @return array
     */
    protected function getServiceInputKeys()
    {
        return array('target', 'action', '_key', '_path', '_method', 'callback');
    }

    /**
     * Prepare input
     *
     * @param array  $data    Data
     * @param string $method  Method
     * @param mixed  $context Entity or entity list OPTIONAL
     *
     * @return array
     */
    protected function prepareInput(array $data, $method, $context = null)
    {
        $checked = true;
        $data = $this->filterInput($data, $method);

        $method = 'prepareInputFor' . ucfirst($method);
        if (method_exists($this, $method)) {
            list($checked, $data) = $this->$method($data, $context);
        }

        return array($checked, $data);
    }

    /**
     * Filter input
     *
     * @param array  $data   Data
     * @param string $method Method name
     *
     * @return array
     */
    protected function filterInput(array $data, $method)
    {
        $method = 'getFilterKeysFor' . ucfirst($method);

        if (method_exists($this, $method)) {
            $data = array_intersect_key($data, array_flip($this->$method()));
        }

        return $data;
    }

    // }}}

    // {{{ Output

    /**
     * Print REST request 
     * 
     * @param mixed $data Data
     *  
     * @return void
     */
    protected function printRESTRequest($data)
    {
        header('Allow: GET,POST,PUT,DELETE');
        header('Date: ' . date('r', \XLite\Core\Converter::time()));

        if (is_array($data)) {
            header('X-Result-Count: ', count($data));
        }

        if (
            !empty($_SERVER['HTTP_ACCEPT'])
            && 'application/xml' == $_SERVER['HTTP_ACCEPT']
            && empty(\XLite\Core\Request::getInstance()->callback)
        ) {
            header('Content-Type: application/xml;charset=utf-8');
            $data = $this->convertToXML();

        } elseif (!empty(\XLite\Core\Request::getInstance()->callback)) {
            header('Content-Type: text/javascript;charset=utf-8');
            $data = $this->convertToJSONP($data);

        } else {
            header('Content-Type: application/json;charset=utf-8');
            $data = $this->convertToJSON($data);
        }

        $this->printRawRESTRequest($data);
    }

    /**
     * Convert data To XML 
     * 
     * @param mixed $data Data
     *  
     * @return void
     */
    protected function convertToXML($data)
    {
        $result = '<' . '?xml version="1.1" encoding="UTF-8" ?' . '>';

        if (is_array($data)) {
            $result .= $this->convertToXMLArray($data);

        } else {
            $result .= $this->convertToXMLCell('body', $data);
        }

        return $result;
    }

    /**
     * Convert to XML array 
     * 
     * @param array $data Data
     *  
     * @return string
     */
    protected function convertToXMLArray(array $data)
    {
        $result = '';

        foreach ($data as $name => $value) {
            $result .= $this->convertToXMLCell($name, $value);
        }

        return $result;
    }

    /**
     * Convert to XML cell 
     * 
     * @param string $name  Cell name
     * @param mixed  $value Cell value
     *  
     * @return string
     */
    protected function convertToXMLCell($name, $value)
    {
        $type = gettype($value);
        $result = '<' . $name . ' type="' . $type .'">';

        if (is_scalar($value)) {

            switch ($type) {
                case 'boolean':
                    $result .= $value ? 'true' : 'false';
                    break;

                case 'integer':
                case 'double':
                    $result .= $value;
                    break;

                case 'string':
                    $result .= htmlspecialchars($value, ENT_XML1);
                    break;

                default:
            }

        } elseif (is_array($value)) {
            $result .= $this->convertToXMLArray($value);
        }

        return $result . '</' . $name . '>';
    }

    /**
     * Convert to JSON 
     * 
     * @param mixed $data Data
     *  
     * @return string
     */
    protected function convertToJSON($data)
    {
        return json_encode($data);
    }

    /**
     * Convert to JSONP
     *
     * @param mixed $data Data
     *
     * @return string
     */
    protected function convertToJSONP($data)
    {
        return \XLite\Core\Request::getInstance()->callback . '(' . $this->convertToJSON($data) . ');';
    }

    /**
     * Print raw REST request 
     * 
     * @param string $data Data
     *  
     * @return void
     */
    protected function printRawRESTRequest($data)
    {
        header('Accept-Ranges: bytes');

        $data = $this->processResponseRange($data);

        header('Last-Modified: ' . date('r', $this->getLastModified()));
        header('ETag: ' . $this->getETag($data));

        if (!$this->processAs304($data)) {
            header('Content-Length: ' . strlen($data));
            header('Content-MD5: ' . base64_encode(md5($data, true)));
            print ($data);
        }
    }

    /**
     * Process response range 
     * 
     * @param string $data Data
     *  
     * @return string
     */
    protected function processResponseRange($data)
    {
        if (!empty($_SERVER['HTTP_RANGE']) && preg_match('/^bytes=(.+)$/Ss', $_SERVER['HTTP_RANGE'], $match)) {
            $ranges = explode(',', $match[1]);
            $min = null;
            $max = null;

            foreach (explode(',', $match[1]) as $range) {
                list($r1, $r2) = explode('-', $range);
                if (!$r1) {
                    $r1 = 0;
                }
                if (!$r2) {
                    $r2 = strlen($data) - 1;
                }

                if (!isset($min) || $r1 < $min) {
                    $min = $r1;
                }

                if (!isset($max) || $r2 > $max) {
                    $max = $r1;
                }
            }

            if (!isset($min)) {
                $min = 0;
            }

            if (!isset($max)) {
                $max = strlen($data) - 1;
            }

            $max = min($max, strlen($data) - 1);

            header('Content-Range: bytes=' . $min . '-' . $max, true, 206);
            $data = substr($data, $min, $max - $min + 1);
        }

        return $data;
    }

    /**
     * Process request as 304 
     * 
     * @param string $data Data
     *  
     * @return boolean
     */
    protected function processAs304($data)
    {
        $result = false;

        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $result = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) > $this->getLastModified();
        }

        if (!$result && !empty($_SERVER['HTTP_IF_NONE_MATCH'])) {
            $result = $_SERVER['HTTP_IF_NONE_MATCH'] == $this->getETag($data);
        }

        if ($result) {
            header('Status: 304 Not Modified', true, 304);
        }

        return $result;
    }

    /**
     * Get last modified time
     * 
     * @return integer
     */
    protected function getLastModified()
    {
        if (!isset($this->lastModified)) {
            $this->lastModified = $this->defineLastModified();
        }

        return $this->lastModified;
    }

    /**
     * Define last modified time
     * 
     * @return integer
     */
    protected function defineLastModified()
    {
        return \XLite\Core\Converter::time();
    }

    /**
     * Get data ETag 
     * 
     * @param string $data Data
     *  
     * @return string
     */
    protected function getETag($data)
    {
        return md5($data);
    }

    // }}}

    // {{{ Requests

    // {{{ GET

    /**
     * Process getAll REST request 
     * 
     * @return array
     */
    protected function processGetAllRESTRequest()
    {
        $result = array();

        foreach ($this->findForGetAll() as $model) {
            $model = is_array($model) ? $model[0] : $model;
            $result[] = $this->convertModelForGetAll($model);
        }

        return $result;    
    }

    /**
     * Process getOne REST request 
     * 
     * @return array
     */
    protected function processGetOneRESTRequest()
    {
        return $this->convertModel($this->findForGetOne());
    }

    /**
     * Find data for getAll request
     * 
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    protected function findForGetAll()
    {
        return $this->getPath()->repository->findAllForREST();
    }

    /**
     * Find data for getOne request
     * 
     * @return \XLite\Model\AEntity
     */
    protected function findForGetOne()
    {
        return $this->getPath()->id ? $this->getPath()->repository->findOneForREST($this->getPath()->id) : null;
    }

    /**
     * Convert model for getAll 
     * 
     * @param \XLite\Model\AEntity $entity Entity
     *  
     * @return array
     */
    protected function convertModelForGetAll(\XLite\Model\AEntity $entity)
    {
        return $this->convertModel($entity, false);
    }

    /**
     * Convert model for getOne
     *
     * @param \XLite\Model\AEntity $entity Entity
     *
     * @return array
     */
    protected function convertModelForGetOne(\XLite\Model\AEntity $entity)
    {
        return $this->convertModel($entity);
    }

    // }}}

    // {{{ POST

    /**
     * Process postAll REST request
     *
     * @return array
     */
    protected function processPostAllRESTRequest()
    {
        $response = array();

        foreach ($this->getInput() as $id => $row) {
            list($checked, $data) = $this->prepareInput($row, 'postAll');
            if ($checked) {
                $entity = $this->createEntity();
                $this->getPath()->repository->loadRawFixture($entity, $data);
                $response[$id] = $entity;
            }
        }

        \XLite\Core\Database::getEM()->flush();

        $this->callPostprocessMethod('post');

        foreach ($response as $id => $entity) {
            $response[$id] = $this->convertModelForPostAll($entity);
        }

        return $response;
    }

    /**
     * Process postOne REST request
     *
     * @return array
     */
    protected function processPostOneRESTRequest()
    {
        $response = null;

        list($checked, $data) = $this->prepareInput($this->getInput(), 'postOne');
        if ($checked) {
            $entity = $this->createEntity();
            $this->getPath()->repository->loadRawFixture($entity, $data);
            \XLite\Core\Database::getEM()->flush();

            $this->callPostprocessMethod('post');

            $response = $this->convertModelForPostOne($entity);
        }

        return $response;
    }

    /**
     * Convert model for postAll
     *
     * @param \XLite\Model\AEntity $entity Entity
     *
     * @return array
     */
    protected function convertModelForPostAll(\XLite\Model\AEntity $entity)
    {
        return $this->convertModel($entity, false);
    }

    /**
     * Convert model for postOne
     *
     * @param \XLite\Model\AEntity $entity Entity
     *
     * @return array
     */
    protected function convertModelForPostOne(\XLite\Model\AEntity $entity)
    {
        return $this->convertModel($entity);
    }

    // }}}

    // {{{ PUT

    /**
     * Process putAll REST request
     *
     * @return array
     */
    protected function processPutAllRESTRequest()
    {
        $response = array();

        foreach ($this->getInput() as $id => $row) {
            list($checked, $data) = $this->prepareInput($row, 'putAll');
            if ($checked) {
                $idName = $this->getPath()->repository->getPrimaryKeyField();
                if (!empty($data[$idName])) {
                    $entity = $this->findForPutOne($data[$idName]);
                    if ($entity) {
                        $this->getPath()->repository->loadRawFixture($entity, $data);
                        $response[$id] = $entity;
                    }
                }
            }
        }

        \XLite\Core\Database::getEM()->flush();

        foreach ($response as $id => $entity) {
            $response[$id] = $this->convertModelForPutAll($entity);
        }

        return $response;
    }

    /**
     * Process putOne REST request
     *
     * @return array
     */
    protected function processPutOneRESTRequest()
    {
        $response = null;

        list($checked, $data) = $this->prepareInput($this->getInput(), 'putOne');
        if ($checked) {
            $entity = $this->findForPutOne($this->getPath()->id);
            if ($entity) {
                $this->getPath()->repository->loadRawFixture($entity, $data);
                \XLite\Core\Database::getEM()->flush();
                $response = $this->convertModelForPutOne($entity);
            }
        }

        return $response;
    }

    /**
     * Find data for putOne request
     *
     * @return \XLite\Model\AEntity
     */
    protected function findForPutOne($id)
    {
        return $this->getPath()->repository->findOneForREST($id);
    }

    /**
     * Convert model for putAll
     *
     * @param \XLite\Model\AEntity $entity Entity
     *
     * @return array
     */
    protected function convertModelForPutAll(\XLite\Model\AEntity $entity)
    {
        return $this->convertModel($entity, false);
    }

    /**
     * Convert model for putOne
     *
     * @param \XLite\Model\AEntity $entity Entity
     *
     * @return array
     */
    protected function convertModelForPutOne(\XLite\Model\AEntity $entity)
    {
        return $this->convertModel($entity);
    }

    // }}}

    // {{{ DELETE

    /**
     * Process deleteAll REST request
     *
     * @return integer
     */
    protected function processDeleteAllRESTRequest()
    {
        $i = 0;

        foreach ($this->findForDeleteAll() as $entity) {
            $entity = is_array($entity) ? $entity[0] : $entity;
            \XLite\Core\Database::getEM()->remove($entity);
            $i++;
        }

        \XLite\Core\Database::getEM()->flush();

        return $i;
    }

    /**
     * Process deleteOne REST request
     *
     * @return array
     */
    protected function processDeleteOneRESTRequest()
    {
        $response = null;

        $entity = $this->findForDeleteOne($this->getPath()->id);
        if ($entity) {
            \XLite\Core\Database::getEM()->remove($entity);
            \XLite\Core\Database::getEM()->flush();
            $response = true;
        }

        return $response;
    }

    /**
     * Find data for deleteAll request
     *
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    protected function findForDeleteAll()
    {
        return $this->getPath()->repository->findAllForREST();
    }

    /**
     * Find data for deleteOne request
     *
     * @return \XLite\Model\AEntity
     */
    protected function findForDeleteOne($id)
    {
        return $this->getPath()->repository->findOneForREST($id);
    }

    // }}}

    // {{{ Common methods routines

    /**
     * Create entity
     *
     * @return \XLite\Model\AEntity
     */
    protected function createEntity()
    {
        $class = $this->getPath()->class;

        return new $class;
    }

    /**
     * Convert model 
     * 
     * @param \XLite\Model\AEntity $model            Model OPTIONAL
     * @param boolean              $withAssociations Convert with associations OPTIONAL
     *  
     * @return mixed
     */
    protected function convertModel(\XLite\Model\AEntity $model = null, $withAssociations = true)
    {
        return $model ? $model->buildDataForREST($withAssociations) : null;
    }

    // }}}

    // }}}

}

