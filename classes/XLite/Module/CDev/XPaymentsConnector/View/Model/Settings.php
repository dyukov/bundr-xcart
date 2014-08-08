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

namespace XLite\Module\CDev\XPaymentsConnector\View\Model;

/**
 * Export payment methods form
 */
class Settings extends \XLite\View\Model\ModuleSettings
{
    /**
     * Return fields list by the corresponding schema
     *
     * @return array
     */
    protected function getFormFieldsForSectionDefault()
    {
        $formFields= parent::getFormFieldsForSectionDefault();

        $sectionFields = \XLite\Module\CDev\XPaymentsConnector\Core\Settings::getInstance()
            ->getFieldsForSection(\XLite\Core\Request::getInstance()->section);

        foreach ($formFields as $field => $data) {
            // Remove fields from other sections
            if (!in_array($field, $sectionFields)) {
                unset($formFields[$field]);
            }
        }

        return $formFields;
    }

    /**
     * Get editable options
     *
     * @return array
     */
    protected function getEditableOptions()
    {
        $options = parent::getEditableOptions();

        $sectionOptions = \XLite\Module\CDev\XPaymentsConnector\Core\Settings::getInstance()
            ->getFieldsForSection(\XLite\Core\Request::getInstance()->section);

        foreach ($options as $key => $option) {
            // Remove options from other sections
            if (!in_array($option->name, $sectionOptions)) {
                unset($options[$key]);
            }
        }

        return $options;
    }

    /**
     * Return list of the "Button" widgets
     *
     * @return array
     */
    protected function getFormButtons()
    {
        $result = parent::getFormButtons();

        $result['addons-list'] = new \XLite\View\Button\BackToModulesLink(
            array(
                \XLite\View\Button\BackToModulesLink::PARAM_MODULE_ID => $this->getModuleID(),
                \XLite\View\Button\AButton::PARAM_STYLE               => 'action addons-list-back-button',
            )
        );

        if (\XLite\Module\CDev\XPaymentsConnector\Core\Settings::SECTION_CONNECTION == \XLite\Core\Request::getInstance()->section) {
            $result['submit'] = new \XLite\View\Button\Submit(
                array(
                    \XLite\View\Button\AButton::PARAM_LABEL => 'Submit and test module',
                    \XLite\View\Button\AButton::PARAM_STYLE => 'action',
                )
            );

        } elseif (\XLite\Module\CDev\XPaymentsConnector\Core\Settings::SECTION_PAYMENT_METHODS == \XLite\Core\Request::getInstance()->section) {
            $result = array();
        }

        return $result;
    }

    /**
     * Return name of web form widget class
     *
     * @return string
     */
    protected function getFormClass()
    {
        return '\XLite\Module\CDev\XPaymentsConnector\View\Form\Settings';
    }
}
