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

namespace XLite\Module\CDev\Coupons\View\Model;

/**
 * Coupon
 */
class Coupon extends \XLite\View\Model\AModel
{
    /**
     * Shema default
     *
     * @var   array
     */
    protected $schemaDefault = array(
        'code' => array(
            self::SCHEMA_CLASS    => 'XLite\Module\CDev\Coupons\View\FormField\Code',
            self::SCHEMA_LABEL    => 'Code',
            self::SCHEMA_REQUIRED => true,
        ),
        'comment' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Comment',
            self::SCHEMA_HELP     => 'This comment will be visible to shop administrators only',
        ),
        'enabled' => array(
            self::SCHEMA_CLASS    => 'XLite\Module\CDev\Coupons\View\FormField\Enabled',
            self::SCHEMA_LABEL    => 'Enabled',
        ),
        'type' => array(
            self::SCHEMA_CLASS    => 'XLite\Module\CDev\Coupons\View\FormField\DiscountType',
            self::SCHEMA_LABEL    => 'Discount type',
            self::SCHEMA_REQUIRED => true,
        ),
        'value' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text\Float',
            self::SCHEMA_LABEL    => 'Discount amount',
            self::SCHEMA_REQUIRED => true,
            \XLite\View\FormField\Input\Text\Float::PARAM_MIN => 0.001,
        ),
        'dateRangeBegin' => array(
            self::SCHEMA_CLASS    => 'XLite\Module\CDev\Coupons\View\FormField\Date',
            self::SCHEMA_LABEL    => 'Active from',
            self::SCHEMA_HELP     => 'Date when customers can start using the coupon',
        ),
        'dateRangeEnd' => array(
            self::SCHEMA_CLASS    => 'XLite\Module\CDev\Coupons\View\FormField\Date',
            self::SCHEMA_LABEL    => 'Active till',
            self::SCHEMA_HELP     => 'Date when the coupon expires',
        ),
        'totalRangeBegin' => array(
            self::SCHEMA_CLASS    => 'XLite\Module\CDev\Coupons\View\FormField\Total',
            self::SCHEMA_LABEL    => 'Subtotal range (begin)',
            \XLite\View\FormField\Input\Text\Float::PARAM_MIN => 0,
            self::SCHEMA_HELP     => 'Minimum order subtotal the coupon can be applied to',
        ),
        'totalRangeEnd' => array(
            self::SCHEMA_CLASS    => 'XLite\Module\CDev\Coupons\View\FormField\Total',
            self::SCHEMA_LABEL    => 'Subtotal range (end)',
            \XLite\View\FormField\Input\Text\Float::PARAM_MIN => 0,
            self::SCHEMA_HELP     => 'Maximum order subtotal the coupon can be applied to',
        ),
        'usesLimitCheck' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Checkbox',
            self::SCHEMA_LABEL    => 'Limit the number of uses',
            \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'input uses-limit-check',
        ),
        'usesLimit' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text\Integer',
            self::SCHEMA_LABEL    => 'The maximum number of uses',
            \XLite\View\FormField\Input\Text\Float::PARAM_MIN     => 0,
            \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'input uses-limit',
        ),
        'productClasses' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Select\ProductClasses',
            self::SCHEMA_LABEL    => 'Product classes',
            self::SCHEMA_HELP     => 'Coupon discount can be limited to these product classes',
        ),
        'memberships' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Select\Memberships',
            self::SCHEMA_LABEL    => 'Memberships',
            self::SCHEMA_HELP     => 'Coupon discount can be limited to customers with these membership levels',
        ),
    );

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'modules/CDev/Coupons/coupon/controller.js';

        return $list;
    }

    /**
     * Return current model ID
     *
     * @return integer
     */
    public function getModelId()
    {
        return \XLite\Core\Request::getInstance()->id;
    }

    /**
     * This object will be used if another one is not pased
     *
     * @return \XLite\Module\CDev\Coupons\Model\Coupon
     */
    protected function getDefaultModelObject()
    {
        $model = $this->getModelId()
            ? \XLite\Core\Database::getRepo('XLite\Module\CDev\Coupons\Model\Coupon')->find($this->getModelId())
            : null;

        return $model ?: new \XLite\Module\CDev\Coupons\Model\Coupon;
    }

    /**
     * Return name of web form widget class
     *
     * @return string
     */
    protected function getFormClass()
    {
        return '\XLite\Module\CDev\Coupons\View\Form\Coupon';
    }

    /**
     * Return list of the "Button" widgets
     *
     * @return array
     */
    protected function getFormButtons()
    {
        $result = parent::getFormButtons();

        $label = $this->getModelObject()->getId() ? 'Update' : 'Create';

        $result['submit'] = new \XLite\View\Button\Submit(
            array(
                \XLite\View\Button\AButton::PARAM_LABEL => $label,
                \XLite\View\Button\AButton::PARAM_STYLE => 'action',
            )
        );

        return $result;
    }

    /**
     * Populate model object properties by the passed data
     *
     * @param array $data Data to set
     *
     * @return void
     */
    protected function setModelProperties(array $data)
    {
        $productClasses = $data['productClasses'];
        $memberships = $data['memberships'];
        unset($data['productClasses'], $data['memberships']);

        if (!empty($data['dateRangeEnd'])) {
            $data['dateRangeEnd'] = mktime(
                23,
                59,
                59,
                date('n', $data['dateRangeEnd']),
                date('j', $data['dateRangeEnd']),
                date('Y', $data['dateRangeEnd'])
            );
        }

        if (empty($data['usesLimitCheck'])) {
            $data['usesLimit'] = 0;
        }

        parent::setModelProperties($data);

        $entity = $this->getModelObject();

        // Product classes
        foreach ($entity->getProductClasses() as $class) {
            $class->getCoupons()->removeElement($entity);
        }
        $entity->getProductClasses()->clear();

        if (is_array($productClasses)) {
            foreach ($productClasses as $id) {
                $class = \XLite\Core\Database::getRepo('XLite\Model\ProductClass')->find($id);
                if ($class) {
                    $entity->addProductClasses($class);
                    $class->addCoupons($entity);
                }
            }
        }

        // Memberships
        foreach ($entity->getMemberships() as $m) {
            $m->getCoupons()->removeElement($entity);
        }
        $entity->getMemberships()->clear();

        if (is_array($memberships)) {
            foreach ($memberships as $id) {
                $m = \XLite\Core\Database::getRepo('XLite\Model\Membership')->find($id);
                if ($m) {
                    $entity->addMemberships($m);
                    $m->addCoupons($entity);
                }
            }
        }
    }

    /**
     * Prepare posted data for mapping to the object
     *
     * @return array
     */
    protected function prepareDataForMapping()
    {
        $data = parent::prepareDataForMapping();

        list($valid) = $this->getFormField('default', 'dateRangeBegin')->validate();
        if ($valid) {
            $data['dateRangeBegin'] = $this->getFormField('default', 'dateRangeBegin')->getValue();
        }

        list($valid) = $this->getFormField('default', 'dateRangeEnd')->validate();
        if ($valid) {
            $data['dateRangeEnd'] = $this->getFormField('default', 'dateRangeEnd')->getValue();
        }

        return $data;
    }

    /**
     * Check if field is valid and (if needed) set an error message
     *
     * @param array  $data    Current section data
     * @param string $section Current section name
     *
     * @return void
     */
    protected function validateFields(array $data, $section)
    {
        parent::validateFields($data, $section);

        $cell = $data[self::SECTION_PARAM_FIELDS];

        if (
            !$this->errorMessages
            && isset($cell['type'])
            && '%' == $cell['type']->getValue()
            && isset($cell['value'])
            && 100 < intval($cell['value']->getValue())
        ) {
            $this->addErrorMessage('value', 'The discount should be less than 100%', $data);
        }
    }

    /**
     * Add top message
     *
     * @return void
     */
    protected function addDataSavedTopMessage()
    {
        if ('create' != $this->currentAction) {
            \XLite\Core\TopMessage::addInfo('The coupon has been updated');

        } else {
            \XLite\Core\TopMessage::addInfo('The coupon has been added');
        }
    }

    /**
     * Prepare usesLimitCheck field parameters
     *
     * @param array &$data Parameters
     *
     * @return void
     */
    protected function prepareFieldParamsUsesLimitCheck(array &$data)
    {
        $model = $this->getModelObject();
        if ($model && 0 < $model->getUsesLimit()) {
            $data['isChecked'] = true;
        }
    }
}
