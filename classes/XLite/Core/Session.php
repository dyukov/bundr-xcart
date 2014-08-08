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
 * Current session
 */
class Session extends \XLite\Base\Singleton
{
    /**
     * Public session id argument name
     */
    const ARGUMENT_NAME = 'xid';

    /**
     * Referer cookie name
     */
    const LC_REFERER_COOKIE_NAME = 'LCRefererCookie';

    /**
     * Name of the cell to store the cURL error code value
     */
    const CURL_CODE_ERROR = 'curl_code_error_in_session';

    /**
     * Name of the cell to store the cURL error code value
     */
    const CURL_CODE_ERROR_MESSAGE = 'curl_error_message_in_session';

    /**
     * Session
     *
     * @var \XLite\Model\Session
     */
    protected $session;

    /**
     * Currently used form ID
     *
     * @var string
     */
    protected static $xliteFormId;

    /**
     * Language (cache)
     *
     * @var \XLite\Model\Language
     */
    protected $language;

    /**
     * Last form id
     *
     * @var string
     */
    protected $lastFormId;


    /**
     * Get session TTL (seconds)
     *
     * @return integer
     */
    public static function getTTL()
    {
        return 0;
    }

    /**
     * Getter
     *
     * @param string $name Session cell name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->session->$name;
    }

    /**
     * Setter
     *
     * @param string $name  Session cell name
     * @param mixed  $value Value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->session->$name = $value;
    }

    /**
     * Check session cell availability
     *
     * @param string $name Session cell name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->session->$name);
    }

    /**
     * Remove session cell
     *
     * @param string $name Session cell name
     *
     * @return void
     */
    public function __unset($name)
    {
        unset($this->session->$name);
    }

    /**
     * Getter
     * DEPRECATE
     *
     * @param string $name Session cell name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->__get($name);
    }

    /**
     * Setter
     * DEPRECATE
     *
     * @param string $name  Session cell name
     * @param mixed  $value Value
     *
     * @return void
     */
    public function set($name, $value)
    {
        $this->__set($name, $value);
    }

    /**
     * Unset in batch mode
     *
     * @param string $name CEll name
     *
     * @return void
     */
    public function unsetBatch($name)
    {
        call_user_func_array(array($this->session, 'unsetBatch'), func_get_args());
    }

    /**
     * Restart session
     *
     * @return void
     */
    public function restart()
    {
        $this->lastFormId = null;
        if (!\XLite\Core\Database::getEM()->contains($this->session)) {
            try {
                $this->session = \XLite\Core\Database::getEM()->merge($this->session);
            } catch (\Doctrine\ORM\EntityNotFoundException $exception) {
                $this->session = null;
            }
        }
        $old = null;

        if ($this->session) {
            $old = $this->session;
            $oldId = $this->session->getId();
        }

        $this->createSession();
        if ($old) {
            foreach (\XLite\Core\Database::getRepo('XLite\Model\SessionCell')->findById($oldId) as $cell) {
                $cell->setId($this->session->getId());
            }
            \XLite\Core\Database::getEM()->remove($old);
            \XLite\Core\Database::getEM()->flush();
        }

        $this->setCookie();
    }

    /**
     * Stores the cURL error code into session
     *
     * @param integer $code cURL error
     *
     * @return void
     */
    public function storeCURLError($code)
    {
        $this->{static::CURL_CODE_ERROR} = $code;
    }

    /**
     * Returns the cURL error code from session
     *
     * @return integer
     */
    public function getCURLError()
    {
        $result = $this->{static::CURL_CODE_ERROR};
        $this->storeCURLError(null);

        return $result;
    }

    /**
     * Stores the cURL error message into session
     *
     * @param string $msg cURL error message
     *
     * @return void
     */
    public function storeCURLErrorMessage($msg)
    {
        $this->{static::CURL_CODE_ERROR_MESSAGE} = $msg;
    }

    /**
     * Returns the cURL error message from session
     *
     * @return string
     */
    public function getCURLErrorMessage()
    {
        $result = $this->{static::CURL_CODE_ERROR_MESSAGE};
        $this->storeCURLErrorMessage(null);

        return $result;
    }

    /**
     * Get public session id argument name
     *
     * @return string
     */
    public function getName()
    {
        return self::ARGUMENT_NAME;
    }

    /**
     * Get public session id
     *
     * @return string
     */
    public function getID()
    {
        return $this->session->getSid();
    }

    /**
     * Load session by public session id
     *
     * @param string $sid Public session id
     *
     * @return boolean
     */
    public function loadBySid($sid)
    {
        $session = \XLite\Core\Database::getRepo('XLite\Model\Session')->findOneBy(
            array(
                'sid' => $sid,
            )
        );

        $result = false;

        if ($session) {
            $result = true;

            \XLite\Core\Database::getEM()->remove($this->session);
            \XLite\Core\Database::getEM()->flush();

            $this->session = $session;
            $this->lastFormId = null;
            $this->setCookie();
        }

        return $result;
    }

    /**
     * Create form id
     *
     * @param boolean $force Flag for forcing form id creation
     *
     * @return string Form id
     */
    public function createFormId($force = false)
    {
        if (!isset($this->lastFormId) || $force) {
            $formId = new \XLite\Model\FormId;
            $formId->setSessionId($this->getSessionFormId());

            \XLite\Core\Database::getEM()->persist($formId);
            \XLite\Core\Database::getEM()->flush($formId);

            $this->lastFormId = $formId->getFormId();
        }

        return $this->lastFormId;
    }

    /**
     * Restore form id 
     * 
     * @return string
     */
    public function restoreFormId()
    {
        $request = \XLite\Core\Request::getInstance();

        if (!empty($request->{\XLite::FORM_ID})) {
            $formId = new \XLite\Model\FormId;
            $formId->setFormId($request->{\XLite::FORM_ID});
            $formId->setSessionId($this->getSessionFormId());

            \XLite\Core\Database::getEM()->persist($formId);
            \XLite\Core\Database::getEM()->flush($formId);

            $this->lastFormId = $formId->getFormId();
        }

        return $this->lastFormId;
    }

    /**
     * Session ID for forms
     *
     * @return integer
     */
    public function getSessionFormId()
    {
        return $this->session->getId();
    }

    /**
     * Get model
     *
     * @return \XLite\Model\Session
     */
    public function getModel()
    {
        return $this->session;
    }

    /**
     * Get language
     *
     * @return \XLite\Model\Language
     */
    public function getLanguage()
    {
        if (!isset($this->language)) {
            $this->language = \XLite\Core\Database::getRepo('XLite\Model\Language')
                ->findOneByCode($this->getCurrentLanguage());
        }

        return $this->language;
    }

    /**
     * Set language
     *
     * @param string $language Language code
     * @param string $zone     Admin/customer zone OPTIONAL
     *
     * @return void
     */
    public function setLanguage($language, $zone = null)
    {
        $code = $this->__get('language');

        if (!isset($zone)) {
            $zone = \XLite::isAdminZone() ? 'admin' : 'customer';
        }

        if (!is_array($code)) {
            $code = array();
        }

        if (!isset($code[$zone]) || $code[$zone] !== $language) {
            $code[$zone] = $language;

            $this->__set('language', $code);
            $this->language = null;
        }
    }

    /**
     * Update language in customer sessions
     *
     * @return void
     */
    public function updateSessionLanguage()
    {
        $list = array();

        foreach (\XLite\Core\Database::getRepo('\XLite\Model\SessionCell')->findByName('language') as $cell) {
            $data = $cell->getValue() ?: array();

            if (isset($data['customer'])) {
                $data['customer'] = \XLite\Core\Config::getInstance()->General->default_language;
                $cell->setValue($data);

                $list[] = $cell;
            }
        }

        \XLite\Core\Database::getRepo('\XLite\Model\SessionCell')->updateInBatch($list);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function __construct()
    {
        $this->clearGarbage();

        if (!$this->restoreSession()) {
            $this->createSession();
        }

        $this->runCronTasks();

        $this->setCookie();
    }

    /**
     * Clear expired sessions and other obsolete data
     *
     * @return void
     */
    protected function clearGarbage()
    {
        \XLite\Core\Database::getRepo('XLite\Model\Session')->removeExpired();
    }

    /**
     * Restore session
     *
     * @return boolean
     */
    protected function restoreSession()
    {
        $this->session = null;

        list($session, $source) = $this->detectPublicSession();

        if ($session) {
            $this->session = $session;

            if ($this->session) {
                if ($source == 'COOKIE') {

                    // DO NOT Change the current session if the $sid goes from COOKIE
                    $this->session->updateExpiry();
                    \XLite\Core\Database::getEM()->flush($this->session);

                } else {

                    // Change the current session if the $sid goes NOT from COOKIE (POST, GET)
                    $this->restart();
                }
            }
        }

        return isset($this->session);
    }

    /**
     * Detect public session
     *
     * @return array (public session model object and source)
     */
    protected function detectPublicSession()
    {
        $sid = null;
        $source = null;
        $arg = $this->getName();

        foreach (array('POST', 'GET', 'COOKIE') as $key) {
            if (isset($GLOBALS['_' . $key][$arg])) {
                $sid = $GLOBALS['_' . $key][$arg];
                $source = $key;
                break;
            }
        }

        $session = \XLite\Core\Database::getRepo('XLite\Model\Session')->findOneBySid($sid);

        // If the $session is null and $source is not a cookie
        //      for example:
        //      $sid is from GET but it is expired right now
        // Then $sid is verified from COOKIE (the user can be logged in already and cookie stores this auth info)
        if (!$session && 'COOKIE' !== $source && isset($GLOBALS['_COOKIE'][$arg])) {
            $sid    = $GLOBALS['_COOKIE'][$arg];
            $source = 'COOKIE';

            $session = \XLite\Core\Database::getRepo('XLite\Model\Session')->findOneBySid($sid);
        }

        return array($session, $source);
    }

    /**
     * Create session
     *
     * @return void
     */
    protected function createSession()
    {
        $this->session = new \XLite\Model\Session();
        $this->session->updateExpiry();
        $this->session->setSid(\XLite\Core\Database::getRepo('XLite\Model\Session')->generatePublicSessionId());

        \XLite\Core\Database::getEM()->persist($this->session);
        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Set cookie
     *
     * @return void
     */
    protected function setCookie()
    {
        if (
            !headers_sent()
            && 'cli' != PHP_SAPI
        ) {
            $arg = $this->getName();

            $httpDomain = $this->getCookieDomain();
            $httpsDomain = $this->getCookieDomain(true);
            $ttl = static::getTTL();

            setcookie(
                $arg,
                $this->getID(),
                $ttl,
                $this->getCookiePath(),
                $httpDomain,
                false,
                true
            );

            if ($httpDomain != $httpsDomain) {
                setcookie(
                    $arg,
                    $this->getID(),
                    $ttl,
                    $this->getCookiePath(true),
                    $httpsDomain,
                    false,
                    true
                );
            }

            $this->setLCRefererCookie();
        }
    }

    /**
     * Set referer cookie (this is stored when user register new profile)
     *
     * @return void
     */
    protected function setLCRefererCookie()
    {
        if (!isset($_COOKIE[self::LC_REFERER_COOKIE_NAME]) && isset($_SERVER['HTTP_REFERER'])) {

            $referer = parse_url($_SERVER['HTTP_REFERER']);

            if (isset($referer['host']) && $referer['host'] != $_SERVER['HTTP_HOST']) {
                setcookie(
                    self::LC_REFERER_COOKIE_NAME,
                    $_SERVER['HTTP_REFERER'],
                    $this->getLCRefererCookieTTL(),
                    $this->getCookiePath(),
                    $this->getCookieDomain(),
                    false,
                    true
                );
            }
        }
    }

    /**
     * Get parsed URL for Set-Cookie
     *
     * @param boolean $secure Secure protocol or not OPTIONAL
     *
     * @return array
     */
    protected function getCookieURL($secure = false)
    {
        $url = $secure
            ? 'http://' .  \XLite::getInstance()->getOptions(array('host_details', 'http_host'))
            : 'https://' . \XLite::getInstance()->getOptions(array('host_details', 'https_host'));

        $url .= \XLite::getInstance()->getOptions(array('host_details', 'web_dir'));

        return parse_url($url);
    }

    /**
     * Get host / domain for Set-Cookie
     *
     * @param boolean $secure Secure protocol or not OPTIONAL
     *
     * @return string
     */
    protected function getCookieDomain($secure = false)
    {
        $url = $this->getCookieURL($secure);

        return false === strstr($url['host'], '.') ? false : $url['host'];
    }

    /**
     * Get URL path for Set-Cookie
     *
     * @param boolean $secure Secure protocol or not OPTIONAL
     *
     * @return string
     */
    protected function getCookiePath($secure = false)
    {
        $url = $this->getCookieURL($secure);

        return isset($url['path']) ? $url['path'] : '/';
    }

    /**
     * Get referer cookie TTL (seconds)
     *
     * @return integer
     */
    protected function getLCRefererCookieTTL()
    {
        return \XLite\Core\Converter::time() + 3600 * 24 * 180; // TTL is 180 days
    }

    /**
     * Get current language
     *
     * @return string Language code
     */
    protected function getCurrentLanguage()
    {
        $code = $this->__get('language');
        $zone = \XLite::isAdminZone() ? 'admin' : 'customer';

        if (!is_array($code)) {
            $code = array();
        }

        if (!empty($code[$zone])) {
            $language = \XLite\Core\Database::getRepo('XLite\Model\Language')->findOneByCode($code[$zone]);

            if (!isset($language) || !$language->getAdded() || !$language->getEnabled()) {
                unset($code[$zone]);
            }
        }

        if (empty($code[$zone])) {
            $this->setLanguage($this->defineCurrentLanguage());
            $code = $this->__get('language');
        }

        return $code[$zone];
    }

    /**
     * Define current language
     *
     * @return string Language code
     */
    protected function defineCurrentLanguage()
    {
        $langs = \XLite\Core\Database::getRepo('XLite\Model\Language')->findActiveLanguages();
        if (!\XLite::isAdminZone() && !empty($langs)) {
            $result = \Includes\Utils\ArrayManager::searchInObjectsArray(
                $langs,
                'getCode',
                isset(\XLite\Core\Config::getInstance()->General)
                    ? \XLite\Core\Config::getInstance()->General->default_language
                    : 'en'
            );
        }

        return isset($result) ? $result->getCode() : static::getDefaultLanguage();
    }

    // {{{ Cron tasks

    /**
     * Run cron tasks
     *
     * @return void
     */
    protected function runCronTasks()
    {
        if ($this->isCronActive()) {
            foreach ($this->getCronTasks() as $method) {
                $result = $this->$method();
            }
        }
    }

    /**
     * Return true if cron tasks should be run
     *
     * @return boolean
     */
    protected function isCronActive()
    {
        // Run cron tasks once per 100 sessions
        return !\XLite\Core\Request::getInstance()->isCLI()
            && \XLite\Core\Config::getInstance()->General->internal_cron_enabled
            && 0 === $this->session->getId() % 100;
    }

    /**
     * Get list of cron tasks
     *
     * @return array
     */
    protected function getCronTasks()
    {
        return array(
            'runGarbageCollectOrders',
        );
    }

    /**
     * Run cron task Garbage collect orders
     *
     * @return array
     */
    protected function runGarbageCollectOrders()
    {
        \XLite\Core\Database::getRepo('XLite\Model\Order')->collectGarbage();
    }

    // }}}
}
