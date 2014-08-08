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
 * Mailer core class
 */
class Mailer extends \XLite\Base\Singleton
{
    /**
     * FROM: Site administrator
     */
    const TYPE_PROFILE_CREATED_USER     = 'siteAdmin';
    const TYPE_PROFILE_CREATED_ADMIN    = 'siteAdmin';
    const TYPE_PROFILE_REGISTER_ANONYM  = 'siteAdmin';
    const TYPE_PROFILE_UPDATED_USER     = 'siteAdmin';
    const TYPE_PROFILE_UPDATED_ADMIN    = 'siteAdmin';
    const TYPE_PROFILE_DELETED_ADMIN    = 'siteAdmin';
    const TYPE_FAILED_ADMIN_LOGIN       = 'siteAdmin';
    const TYPE_SAFE_MODE_ACCESS_KEY     = 'siteAdmin';
    const TYPE_FAILED_ORDER_ADMIN       = 'siteAdmin';
    const TYPE_CANCELED_ORDER_ADMIN     = 'siteAdmin';
    const TYPE_PROCESS_ORDER_CUSTOMER   = 'siteAdmin';
    const TYPE_PROCESS_ORDER_ADMIN      = 'siteAdmin';
    const TYPE_ORDER_CREATED_ADMIN      = 'siteAdmin';
    const TYPE_EXPORT_COMPLETED         = 'siteAdmin';
    const TYPE_SHIPPED_ORDER_CUSTOMER   = 'siteAdmin';

    /**
     * FROM: Users department
     */
    const TYPE_RECOVER_PASSWORD_REQUEST     = 'usersDep';
    const TYPE_RECOVER_PASSWORD_CONFIRMATION = 'usersDep';

    /**
     * FROM: Orders department
     */
    const TYPE_ORDER_CREATED_CUSTOMER   = 'ordersDep';
    const TYPE_FAILED_ORDER_CUSTOMER    = 'ordersDep';
    const TYPE_CANCELED_ORDER_CUSTOMER  = 'ordersDep';

    /**
     * Custom from
     */
    const TYPE_TEST_EMAIL   = 'testEmail';

    /**
     * Mailer instance
     *
     * @var \XLite\View\Mailer
     */
    protected static $mailer = null;


    /**
     * Interface to use in mail
     *
     * @var string
     */
    protected static $mailInterface = \XLite::CUSTOMER_INTERFACE;

    /**
     * Send notification about created profile to the user
     *
     * @param \XLite\Model\Profile $profile  Profile object
     * @param string               $password Profile password
     *
     * @return void
     */
    public static function sendProfileCreatedUserNotification(\XLite\Model\Profile $profile, $password = null, $byCheckout = false)
    {
        // Register variables
        static::register('profile', $profile);
        static::register('password', $password);
        static::register('byCheckout', $byCheckout);

        // Compose and send email
        static::compose(
            static::TYPE_PROFILE_CREATED_USER,
            static::getSiteAdministratorMail(),
            $profile->getLogin(),
            'signin_notification',
            array(),
            true,
            \XLite::CUSTOMER_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::CUSTOMER_INTERFACE, $profile->getLanguage())
        );
    }

    /**
     * Send notification about created profile to the users department
     *
     * @param \XLite\Model\Profile $profile Profile object
     *
     * @return void
     */
    public static function sendProfileCreatedAdminNotification(\XLite\Model\Profile $profile)
    {
        static::register('profile', $profile);

        static::compose(
            static::TYPE_PROFILE_CREATED_ADMIN,
            static::getSiteAdministratorMail(),
            static::getUsersDepartmentMail(),
            'signin_admin_notification',
            array(),
            true,
            \XLite::CUSTOMER_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::ADMIN_INTERFACE)
        );
    }

    /**
     * Send notification about created profile to the user
     *
     * @param \XLite\Model\Order $order Order object
     *
     * @return void
     */
    public static function sendOrderTrackingInfoNotification(\XLite\Model\Order $order)
    {
        // Register variables
        static::register('order', $order);
        static::register('trackingNumbers', $order->getTrackingNumbers());
        static::register(
            'orderURL',
            \XLite::getInstance()->getShopURL(\XLite\Core\Converter::buildURL(
                'order',
                '',
                array(
                    'order_number' => $order->getOrderNumber(),
                ),
                \XLite::CART_SELF
            ))
        );

        // Compose and send email
        static::compose(
            static::TYPE_PROFILE_REGISTER_ANONYM,
            static::getSiteAdministratorMail(),
            $order->getProfile()->getLogin(),
            'order_tracking_info',
            array(),
            true,
            \XLite::MAIL_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::CUSTOMER_INTERFACE, $order->getProfile()->getLanguage())
        );

        \XLite\Core\OrderHistory::getInstance()->registerCustomerEmailSent($order->getOrderId(), 'Tracking information is sent to the customer');
    }

    /**
     * Send notification about created profile to the user
     *
     * @param \XLite\Model\Profile $profile  Profile object
     * @param string               $password Profile password
     *
     * @return void
     */
    public static function sendRegisterAnonymousNotification(\XLite\Model\Profile $profile, $password)
    {
        // Register variables
        static::register('profile', $profile);
        static::register('password', $password);

        // Compose and send email
        static::compose(
            static::TYPE_PROFILE_REGISTER_ANONYM,
            static::getSiteAdministratorMail(),
            $profile->getLogin(),
            'register_anonym',
            array(),
            true,
            \XLite::CUSTOMER_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::CUSTOMER_INTERFACE, $profile->getLanguage())
        );
    }

    /**
     * Send notification about updated profile to the user
     *
     * @param \XLite\Model\Profile $profile Profile object
     * @param string               $password Profile password
     *
     * @return void
     */
    public static function sendProfileUpdatedUserNotification(\XLite\Model\Profile $profile, $password = null)
    {
        static::register('profile', $profile);
        static::register('password', $password);

        static::compose(
            static::TYPE_PROFILE_UPDATED_USER,
            static::getSiteAdministratorMail(),
            $profile->getLogin(),
            'profile_modified',
            array(),
            true,
            \XLite::CUSTOMER_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::CUSTOMER_INTERFACE, $profile->getLanguage())
        );
    }

    /**
     * Send notification about updated profile to the users department
     *
     * @param \XLite\Model\Profile $profile Profile object
     *
     * @return void
     */
    public static function sendProfileUpdatedAdminNotification(\XLite\Model\Profile $profile)
    {
        static::register('profile', $profile);

        static::compose(
            static::TYPE_PROFILE_UPDATED_ADMIN,
            static::getSiteAdministratorMail(),
            static::getUsersDepartmentMail(),
            'profile_admin_modified',
            array(),
            true,
            \XLite::CUSTOMER_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::ADMIN_INTERFACE)
        );
    }

    /**
     * Send notification about deleted profile to the users department
     *
     * @param string $userLogin Login of deleted profile
     *
     * @return void
     */
    public static function sendProfileDeletedAdminNotification($userLogin)
    {
        static::register('userLogin', $userLogin);

        static::compose(
            static::TYPE_PROFILE_DELETED_ADMIN,
            static::getSiteAdministratorMail(),
            static::getUsersDepartmentMail(),
            'profile_admin_deleted',
            array(),
            true,
            \XLite::CUSTOMER_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::ADMIN_INTERFACE)
        );
    }

    /**
     * Send notification to the site administrator email about failed administrator login attempt
     *
     * @param string $postedLogin Login that was used in failed login attempt
     *
     * @return void
     */
    public static function sendFailedAdminLoginNotification($postedLogin)
    {
        static::register(
            array(
                'login'                 => isset($postedLogin) ? $postedLogin : 'unknown',
                'REMOTE_ADDR'           => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown',
                'HTTP_X_FORWARDED_FOR'  => isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                    ? $_SERVER['HTTP_X_FORWARDED_FOR']
                    : 'unknown',
            )
        );

        static::compose(
            static::TYPE_FAILED_ADMIN_LOGIN,
            static::getSiteAdministratorMail(),
            static::getSiteAdministratorMail(),
            'login_error',
            array(),
            true,
            \XLite::CUSTOMER_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::ADMIN_INTERFACE)
        );
    }

    /**
     * Send recover password request to the user
     *
     * @param string $userLogin            User email (login)
     * @param string $userPasswordResetKey User password
     *
     * @return void
     */
    public static function sendRecoverPasswordRequest($userLogin, $userPasswordResetKey)
    {
        static::register(
            'url',
            \XLite::getInstance()->getShopURL(
                \XLite\Core\Converter::buildURL('recover_password', 'confirm', array(
                    'email'         => $userLogin,
                    'request_id'    => $userPasswordResetKey,
                ))
            )
        );

        static::compose(
            static::TYPE_RECOVER_PASSWORD_REQUEST,
            static::getUsersDepartmentMail(),
            $userLogin,
            'recover_request'
        );
    }

    /**
     * Send password recovery confirmation to the user
     *
     * @param string $userLogin    User email (login)
     * @param string $userPassword User password (unencrypted)
     *
     * @return void
     */
    public static function sendRecoverPasswordConfirmation($userLogin, $userPassword)
    {
        static::register(
            array(
                'email'         => $userLogin,
                'new_password'  => $userPassword,
            )
        );

        static::compose(
            static::TYPE_RECOVER_PASSWORD_CONFIRMATION,
            static::getUsersDepartmentMail(),
            $userLogin,
            'recover_recover'
        );
    }

    /**
     * Send created order mails.
     *
     * @param \XLite\Model\Order $order Order model
     *
     * @return void
     */
    public static function sendOrderCreated(\XLite\Model\Order $order)
    {
        static::register(
            array('order' => $order,)
        );

        if (\XLite\Core\Config::getInstance()->Company->enable_init_order_notif_customer) {
            static::sendOrderCreatedCustomer($order);
        }

        if (\XLite\Core\Config::getInstance()->Company->enable_init_order_notif) {
            static::sendOrderCreatedAdmin($order);
        }
    }

    /**
     * Send created order mail to customer
     *
     * @param \XLite\Model\Order $order Order model
     *
     * @return void
     */
    public static function sendOrderCreatedCustomer(\XLite\Model\Order $order)
    {
        static::setMailInterface(\XLite::CUSTOMER_INTERFACE);

        static::compose(
            static::TYPE_ORDER_CREATED_CUSTOMER,
            static::getOrdersDepartmentMail(),
            $order->getProfile()->getLogin(),
            'order_created',
            array(),
            true,
            \XLite::MAIL_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::CUSTOMER_INTERFACE, $order->getProfile()->getLanguage())
        );

        \XLite\Core\OrderHistory::getInstance()->registerCustomerEmailSent($order->getOrderId(), 'Order is initially created');
    }

    /**
     * Send created order mail to admin
     *
     * @param \XLite\Model\Order $order Order model
     *
     * @return void
     */
    public static function sendOrderCreatedAdmin(\XLite\Model\Order $order)
    {
        static::setMailInterface(\XLite::ADMIN_INTERFACE);

        static::compose(
            static::TYPE_ORDER_CREATED_ADMIN,
            static::getSiteAdministratorMail(),
            static::getOrdersDepartmentMail(),
            'order_created_admin',
            array(),
            true,
            \XLite::MAIL_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::ADMIN_INTERFACE)
        );

        \XLite\Core\OrderHistory::getInstance()->registerAdminEmailSent($order->getOrderId(), 'Order is initially created');
    }

    /**
     * Send processed order mails
     *
     * @param \XLite\Model\Order $order ____param_comment____
     *
     * @return void
     */
    public static function sendProcessOrder(\XLite\Model\Order $order)
    {
        static::register(
            array('order' => $order,)
        );

        static::sendProcessOrderAdmin($order);

        static::sendProcessOrderCustomer($order);
    }

    /**
     * Send processed order mail to Admin
     *
     * @param \XLite\Model\Order $order Order model
     *
     * @return void
     */
    public static function sendProcessOrderAdmin(\XLite\Model\Order $order)
    {
        static::setMailInterface(\XLite::ADMIN_INTERFACE);

        static::compose(
            static::TYPE_PROCESS_ORDER_ADMIN,
            static::getSiteAdministratorMail(),
            static::getOrdersDepartmentMail(),
            'order_processed',
            array(),
            true,
            \XLite::MAIL_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::ADMIN_INTERFACE)
        );

        \XLite\Core\OrderHistory::getInstance()->registerAdminEmailSent($order->getOrderId(), 'Order is processed');
    }

    /**
     * Send processed order mail to Customer
     *
     * @param \XLite\Model\Order $order Order model
     *
     * @return void
     */
    public static function sendProcessOrderCustomer(\XLite\Model\Order $order)
    {
        static::setMailInterface(\XLite::CUSTOMER_INTERFACE);

        if ($order->getProfile()) {
            static::compose(
                static::TYPE_PROCESS_ORDER_CUSTOMER,
                static::getSiteAdministratorMail(),
                $order->getProfile()->getLogin(),
                'order_processed',
                array(),
                true,
                \XLite::MAIL_INTERFACE,
                static::getMailer()->getLanguageCode(\XLite::CUSTOMER_INTERFACE, $order->getProfile()->getLanguage())
            );

            \XLite\Core\OrderHistory::getInstance()->registerCustomerEmailSent($order->getOrderId(), 'Order is processed');
        }
    }

    /**
     * Send changed order mails
     *
     * @param \XLite\Model\Order $order ____param_comment____
     *
     * @return void
     */
    public static function sendChangedOrder(\XLite\Model\Order $order)
    {
        static::register(
            array('order' => $order,)
        );

        static::sendChangedOrderAdmin($order);

        static::sendChangedOrderCustomer($order);
    }

    /**
     * Send changed order mail to Admin
     *
     * @param \XLite\Model\Order $order Order model
     *
     * @return void
     */
    public static function sendChangedOrderAdmin(\XLite\Model\Order $order)
    {
        static::setMailInterface(\XLite::ADMIN_INTERFACE);

        static::compose(
            static::TYPE_PROCESS_ORDER_ADMIN,
            static::getSiteAdministratorMail(),
            static::getOrdersDepartmentMail(),
            'order_changed',
            array(),
            true,
            \XLite::MAIL_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::ADMIN_INTERFACE)
        );

        \XLite\Core\OrderHistory::getInstance()->registerAdminEmailSent($order->getOrderId(), 'Order is changed');
    }

    /**
     * Send changed order mail to Customer
     *
     * @param \XLite\Model\Order $order Order model
     *
     * @return void
     */
    public static function sendChangedOrderCustomer(\XLite\Model\Order $order)
    {
        static::setMailInterface(\XLite::CUSTOMER_INTERFACE);

        if ($order->getProfile()) {
            static::compose(
                static::TYPE_PROCESS_ORDER_CUSTOMER,
                static::getSiteAdministratorMail(),
                $order->getProfile()->getLogin(),
                'order_changed',
                array(),
                true,
                \XLite::MAIL_INTERFACE,
                static::getMailer()->getLanguageCode(\XLite::CUSTOMER_INTERFACE, $order->getProfile()->getLanguage())
            );

            \XLite\Core\OrderHistory::getInstance()->registerCustomerEmailSent($order->getOrderId(), 'Order is changed');
        }
    }

    /**
     * Send email notification to customer about shipped order
     *
     * @param \XLite\Model\Order $order Order object
     *
     * @return void
     */
    public static function sendShippedOrder(\XLite\Model\Order $order)
    {
        static::register(
            array('order' => $order,)
        );

        static::setMailInterface(\XLite::CUSTOMER_INTERFACE);

        if ($order->getProfile()) {
            static::compose(
                static::TYPE_SHIPPED_ORDER_CUSTOMER,
                static::getSiteAdministratorMail(),
                $order->getProfile()->getLogin(),
                'order_shipped',
                array(),
                true,
                \XLite::MAIL_INTERFACE,
                static::getMailer()->getLanguageCode(\XLite::CUSTOMER_INTERFACE, $order->getProfile()->getLanguage())
            );

            \XLite\Core\OrderHistory::getInstance()->registerCustomerEmailSent($order->getOrderId(), 'Order is shipped');
        }
    }

    /**
     * Send failed order mails
     *
     * @param \XLite\Model\Order $order Order model
     *
     * @return void
     */
    public static function sendFailedOrder(\XLite\Model\Order $order)
    {
        static::register(
            array('order' => $order,)
        );

        static::sendFailedOrderAdmin($order);

        static::sendFailedOrderCustomer($order);
    }

    /**
     * Send failed order mail to Admin
     *
     * @param \XLite\Model\Order $order Order model
     *
     * @return void
     */
    public static function sendFailedOrderAdmin(\XLite\Model\Order $order)
    {
        static::setMailInterface(\XLite::ADMIN_INTERFACE);

        static::compose(
            static::TYPE_FAILED_ORDER_ADMIN,
            static::getSiteAdministratorMail(),
            static::getOrdersDepartmentMail(),
            'order_failed',
            array(),
            true,
            \XLite::MAIL_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::ADMIN_INTERFACE)
        );

        \XLite\Core\OrderHistory::getInstance()->registerAdminEmailSent($order->getOrderId(), 'Order is failed');
    }

    /**
     * Send failed order mail to Customer
     *
     * @param \XLite\Model\Order $order Order model
     *
     * @return void
     */
    public static function sendFailedOrderCustomer(\XLite\Model\Order $order)
    {
        static::setMailInterface(\XLite::CUSTOMER_INTERFACE);

        if ($order->getProfile()) {
            static::compose(
                static::TYPE_FAILED_ORDER_CUSTOMER,
                static::getOrdersDepartmentMail(),
                $order->getProfile()->getLogin(),
                'order_failed',
                array(),
                true,
                \XLite::MAIL_INTERFACE,
                static::getMailer()->getLanguageCode(\XLite::CUSTOMER_INTERFACE, $order->getProfile()->getLanguage())
            );

            \XLite\Core\OrderHistory::getInstance()->registerCustomerEmailSent($order->getOrderId(), 'Order is failed');
        }
    }

    /**
     * Send failed order mails
     *
     * @param \XLite\Model\Order $order Order model
     *
     * @return void
     */
    public static function sendCanceledOrder(\XLite\Model\Order $order)
    {
        static::register(
            array('order' => $order,)
        );

        static::sendCanceledOrderAdmin($order);

        static::sendCanceledOrderCustomer($order);
    }

    /**
     * Send failed order mail to Admin
     *
     * @param \XLite\Model\Order $order Order model
     *
     * @return void
     */
    public static function sendCanceledOrderAdmin(\XLite\Model\Order $order)
    {
        static::setMailInterface(\XLite::ADMIN_INTERFACE);

        static::compose(
            static::TYPE_CANCELED_ORDER_ADMIN,
            static::getSiteAdministratorMail(),
            static::getOrdersDepartmentMail(),
            'order_canceled',
            array(),
            true,
            \XLite::MAIL_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::ADMIN_INTERFACE)
        );

        \XLite\Core\OrderHistory::getInstance()->registerAdminEmailSent($order->getOrderId(), 'Order is canceled');
    }

    /**
     * Send failed order mail to Customer
     *
     * @param \XLite\Model\Order $order Order model
     *
     * @return void
     */
    public static function sendCanceledOrderCustomer(\XLite\Model\Order $order)
    {
        static::setMailInterface(\XLite::CUSTOMER_INTERFACE);

        if ($order->getProfile()) {
            static::compose(
                static::TYPE_CANCELED_ORDER_CUSTOMER,
                static::getOrdersDepartmentMail(),
                $order->getProfile()->getLogin(),
                'order_canceled',
                array(),
                true,
                \XLite::MAIL_INTERFACE,
                static::getMailer()->getLanguageCode(\XLite::CUSTOMER_INTERFACE, $order->getProfile()->getLanguage())
            );

            \XLite\Core\OrderHistory::getInstance()->registerCustomerEmailSent($order->getOrderId(), 'Order is canceled');
        }
    }

    /**
     * Send notification about generated safe mode access key
     *
     * @param string $key Access key
     *
     * @return void
     */
    public static function sendSafeModeAccessKeyNotification($key)
    {
        static::setMailInterface(\XLite::ADMIN_INTERFACE);

        // Register variables
        static::register('key', $key);
        static::register('hard_reset_url', \Includes\SafeMode::getResetURL());
        static::register('soft_reset_url', \Includes\SafeMode::getResetURL(true));
        static::register('article_url', \XLite::getController()->getArticleURL());

        static::compose(
            static::TYPE_SAFE_MODE_ACCESS_KEY,
            static::getSiteAdministratorMail(),
            static::getSiteAdministratorMail(),
            'safe_mode_key_generated',
            array(),
            true,
            \XLite::CUSTOMER_INTERFACE,
            static::getMailer()->getLanguageCode(\XLite::ADMIN_INTERFACE)
        );
    }

    /**
     * Send test email
     *
     * @param string $from Email address to send test email from
     * @param string $to   Email address to send test email to
     * @param string $body Body test email text
     *
     * @return string
     */
    public static function sendTestEmail($from, $to, $body = '')
    {
        static::register(
            array('body' => $body,)
        );

        static::setMailInterface(\XLite::ADMIN_INTERFACE);

        static::compose(
            static::TYPE_TEST_EMAIL,
            $from,
            $to,
            'test_email'
        );

        return static::getMailer()->getLastError();
    }

    /**
     * Send notification about export process completed
     *
     * @return void
     */
    public static function sendExportCompletedNotification()
    {
        static::register('url', \XLite\Core\Converter::buildURL('export', '', array('completed' => 1), \XLite::ADMIN_SELF));

        static::compose(
            static::TYPE_EXPORT_COMPLETED,
            static::getSiteAdministratorMail(),
            static::getSiteAdministratorMail(),
            'export_completed'
        );
    }

    /**
     * Set mail interface
     *
     * @param string $interface Interface to use in mail OPTIONAL
     *
     * @return void
     */
    protected static function setMailInterface($interface = \XLite::CUSTOMER_INTERFACE)
    {
        static::$mailInterface = $interface;
    }


    /**
     * Returns mailer instance
     *
     * @return \XLite\View\Mailer
     */
    protected static function getMailer()
    {
        if (!isset(static::$mailer)) {
            static::$mailer = new \XLite\View\Mailer();
        }

        return static::$mailer;
    }

    /**
     * Register variable into mail viewer
     *
     * @param string $name  Variable name
     * @param mixed  $value Variable value OPTIONAL
     *
     * @return void
     */
    protected static function register($name, $value = '')
    {
        $variables = is_array($name) ? $name : array($name => $value);
        $mailer = static::getMailer();

        foreach ($variables as $name => $value) {
            $mailer->set($name, isset($value) ? $value : false);
        }
    }

    /**
     * Compose and send wrapper for \XLite\View\Mailer::compose()
     *
     * @param string  $type          Email type. It defines the additional specific changes of the email data (see prepareFrom and other methods)
     * @param string  $from          Email FROM
     * @param string  $to            Email TO
     * @param string  $dir           Directory where mail templates are located
     * @param array   $customHeaders Array of custom mail headers OPTIONAL
     * @param boolean $doSend        Flag: if true - send email immediately OPTIONAL
     * @param string  $mailInterface Intarface to compile mail templates (skin name: customer, admin or mail) OPTIONAL
     *
     * @return void
     */
    protected static function compose($type, $from, $to, $dir, $customHeaders = array(), $doSend = true, $mailInterface = \XLite::CUSTOMER_INTERFACE, $languageCode = '')
    {
        static::getMailer()->compose(
            static::prepareFrom($type, $from),
            static::prepareTo($type, $to),
            static::prepareDir($type, $dir),
            static::prepareCustomHeaders($type, $customHeaders),
            $mailInterface,
            $languageCode
        );

        if ($doSend) {
            static::getMailer()->send();
            static::setMailInterface();
        }
    }

    /**
     * Make some specific preparations for "From" field according the email type
     *
     * @param string $type Email notificaion "type"
     * @param string $from "From" field value
     *
     * @return string new "From" field value
     */
    protected static function prepareFrom($type, $from)
    {
        return method_exists('\XLite\Core\Mailer', 'prepareFrom' . ucfirst($type))
            ? call_user_func('\XLite\Core\Mailer::prepareFrom' . ucfirst($type), $from)
            : $from;
    }

    /**
     * Make some specific preparations for "To" field according the email type
     *
     * @param string $type Email notificaion "type"
     * @param string $to   "To" field value
     *
     * @return string new "To" field value
     */
    protected static function prepareTo($type, $to)
    {
        return method_exists('\XLite\Core\Mailer', 'prepareTo' . ucfirst($type))
            ? call_user_func('\XLite\Core\Mailer::prepareTo' . ucfirst($type), $to)
            : $to;
    }

    /**
     * Make some specific preparations for "dir" field according the email type
     *
     * @param string $type Email notificaion "type"
     * @param string $dir  Dir field value
     *
     * @return string new "Dir" field value
     */
    protected static function prepareDir($type, $dir)
    {
        return method_exists('\XLite\Core\Mailer', 'prepareDir' . ucfirst($type))
            ? call_user_func('\XLite\Core\Mailer::prepareDir' . ucfirst($type), $dir)
            : $dir;
    }

    /**
     * Make some specific preparations for "Custom Headers" according the email type
     *
     * @param string $type          Email notificaion "type"
     * @param array  $customHeaders "Custom Headers" field value
     *
     * @return array new "Custom Headers" field value
     */
    protected static function prepareCustomHeaders($type, $customHeaders)
    {
        return method_exists('\XLite\Core\Mailer', 'prepareCustomHeaders' . ucfirst($type))
            ? call_user_func('\XLite\Core\Mailer::prepareCustomHeaders' . ucfirst($type), $customHeaders)
            : $customHeaders;
    }

    /**
     * Sales department e-mail:
     *
     * @return string
     */
    protected static function getOrdersDepartmentMail()
    {
        return \XLite\Core\Config::getInstance()->Company->orders_department;
    }

    /**
     * Customer relations e-mail
     *
     * @return string
     */
    protected static function getUsersDepartmentMail()
    {
        return \XLite\Core\Config::getInstance()->Company->users_department;
    }

    /**
     * Site administrator e-mail
     *
     * @return string
     */
    protected static function getSiteAdministratorMail()
    {
        return \XLite\Core\Config::getInstance()->Company->site_administrator;
    }
}
