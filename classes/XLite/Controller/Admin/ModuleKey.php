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

namespace XLite\Controller\Admin;

/**
 * ModuleKey
 */
class ModuleKey extends \XLite\Controller\Admin\AAdmin
{
    // {{{ Public methods for viewers

    /**
     * Return page title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Enter license key';
    }

    // }}}

    // {{{ "Register key" action handler

    /**
     * Action of view license view
     *
     * @return void
     */
    protected function doActionView()
    {
    }

    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        return array_merge(parent::defineFreeFormIdActions(), array('view'));
    }

    /**
     * Action of license key registration
     *
     * @return void
     */
    protected function doActionRegisterKey()
    {
        $key = \XLite\Core\Request::getInstance()->key;

        if ($key) {
            $keysInfo = \XLite\Core\Marketplace::getInstance()->checkAddonKey($key);

        } else {
            $keysInfo = null;
            $emptyKey = true;
        }

        $this->setReturnURL($this->buildURL('addons_list_marketplace'));

        if ($keysInfo && $keysInfo[$key]) {
            $keysInfo = $keysInfo[$key];
            $repo = \XLite\Core\Database::getRepo('\XLite\Model\ModuleKey');

            foreach ($keysInfo as $info) {
                if (\XLite\Model\ModuleKey::KEY_TYPE_XCN == $info['keyType']) {

                    $xcnPlan = $info['xcnPlan'];
                    $keyData = $info['keyData'];

                    // Unset some fields which is not in database
                    unset($info['xcnPlan']);
                    unset($info['keyData']);
                    unset($info['key']);

                    $entity = $repo->findOneBy($info);

                    if ($entity) {
                        $entity->setKeyValue($key);
                        $entity->setXcnPlan($xcnPlan);
                        $entity->setKeyData($keyData);
                        $repo->update($entity);

                    } else {
                        $entity = $repo->insert($info + array('keyValue' => $key, 'xcnPlan' => $xcnPlan, 'keyData' => $keyData));
                    }

                    // Clear cache for proper installation
                    \XLite\Core\Marketplace::getInstance()->clearActionCache(
                        \XLite\Core\Marketplace::ACTION_GET_ADDONS_LIST
                    );

                    $this->showInfo(
                        __FUNCTION__,
                        'X-Cart license key has been successfully verified'
                    );
                    $this->setHardRedirect();

                } else {
                    $module = \XLite\Core\Database::getRepo('\XLite\Model\Module')->findOneBy(
                        array(
                            'author' => $info['author'],
                            'name'   => $info['name'],
                        )
                    );

                    if ($module) {
                        $entity = $repo->findKey($info['author'], $info['name']);

                        if ($entity) {
                            $entity->setKeyValue($key);
                            $repo->update($entity);

                        } else {
                            $entity = $repo->insert($info + array('keyValue' => $key));
                        }

                        // Clear cache for proper installation
                        \XLite\Core\Marketplace::getInstance()->clearActionCache(
                            \XLite\Core\Marketplace::ACTION_GET_ADDONS_LIST
                        );

                        $this->showInfo(
                            __FUNCTION__,
                            'License key has been successfully verified for "{{name}}" module by "{{author}}" author',
                            array(
                                'name'   => $module->getModuleName(),
                                'author' => $module->getAuthorName(),
                            )
                        );

                        // We install the addon after the successfull key verification
                        $this->setReturnURL(
                            $this->buildURL(
                                'upgrade',
                                'install_addon_force',
                                array(
                                    'moduleIds[]' => $module->getModuleID(),
                                    'agree'       => 'Y',
                                )
                            )
                        );

                    } else {
                        $this->showError(
                            __FUNCTION__,
                            static::t('Key is validated, but the module X was not found', array('module' => implode(',', $info)))
                        );
                    }
                }
            }

        } elseif (!isset($emptyKey)) {
            $error = \XLite\Core\Marketplace::getInstance()->getError();
            $message = $error
                ? static::t('Response from marketplace: X', array('response' => $error))
                : static::t('Response from marketplace is not received');
            $this->showError(__FUNCTION__, $message);

        } else {
            $this->showError(__FUNCTION__, static::t('Please specify non-empty key'));
        }
    }

    // }}}
}
