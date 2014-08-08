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

namespace XLite\View\FormField\Input\Text;

/**
 * Clean URL
 */
class CleanURL extends \XLite\View\FormField\Input\Text
{
    const PARAM_OBJECT_CLASS_NAME = 'objectClassName';
    const PARAM_OBJECT_ID_NAME    = 'objectIdName';

    /**
     * Define widget params
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_OBJECT_CLASS_NAME => new \XLite\Model\WidgetParam\String('Object class name'),
            self::PARAM_OBJECT_ID_NAME    => new \XLite\Model\WidgetParam\String('Object Id name', 'id'),
        );
    }

    /**
     * Return field template
     *
     * @return string
     */
    protected function getFieldTemplate()
    {
        return '/input/text/clean_url.tpl';
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = $this->getDir() . '/input/text/clean_url.js';

        return $list;
    }

    /**
     * Check field validity
     *
     * @return boolean
     */
    protected function checkFieldValidity()
    {
        $result = parent::checkFieldValidity();

        if (
            $result
            && $this->getValue()
        ) {
            $validator = new \XLite\Core\Validator\String\CleanURL(
                false,
                null,
                $this->getParam(self::PARAM_OBJECT_CLASS_NAME),
                \XLite\Core\Request::getInstance()->{$this->getParam(self::PARAM_OBJECT_ID_NAME)}
            );
            try {
                $validator->validate($this->getValue());

            } catch (\XLite\Core\Validator\Exception $exception) {
                $message = static::t($exception->getMessage(), $exception->getLabelArguments());
                $result = false;
                $this->errorMessage = \XLite\Core\Translation::lbl(
                    ($exception->getPublicName() ? static::t($exception->getPublicName()) . ': ' : '') . $message,
                    array(
                        'name' => $this->getLabel(),
                    )
                );
            }
        }

        return $result;
    }

}
