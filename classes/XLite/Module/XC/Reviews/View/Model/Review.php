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

namespace XLite\Module\XC\Reviews\View\Model;

/**
 * Review view model
 *
 */
class Review extends \XLite\View\Model\AModel
{

    /**
     * Schema default
     *
     * @var array
     */
    protected $schemaDefault = array(
        'rating' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text\Integer',
            self::SCHEMA_LABEL    => 'Rating',
            self::SCHEMA_REQUIRED => false,
        ),
        'email' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Email',
            self::SCHEMA_REQUIRED => false,
        ),
        'reviewerName' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Customer name',
            self::SCHEMA_REQUIRED => false,
        ),
        'review' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Textarea\Simple',
            self::SCHEMA_LABEL    => 'Text of review',
            self::SCHEMA_REQUIRED => false,
        ),
        'status' => array(
            self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text\Integer',
            self::SCHEMA_LABEL    => 'Status',
            self::SCHEMA_REQUIRED => false,
        ),
    );

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
     * @return \XLite\Module\XC\Reviews\Model\Review
     */
    protected function getDefaultModelObject()
    {
        $model = $this->getModelId()
            ? \XLite\Core\Database::getRepo('XLite\Module\XC\Reviews\Model\Review')->find($this->getModelId())
            : null;

        return $model
            ? $model
            : new \XLite\Module\XC\Reviews\Model\Review;
    }

    /**
     * Return name of web form widget class
     *
     * @return string
     */
    protected function getFormClass()
    {
        return '\XLite\Module\XC\Reviews\View\Form\Model\Review';
    }

    /**
     * Return list of the "Button" widgets
     *
     * @return array
     */
    protected function getFormButtons()
    {
        $result = parent::getFormButtons();

        if ($this->getModelObject()->getId()) {
            if ($this->isApproved()) {
                $result['submit'] = new \XLite\View\Button\Submit(
                    array(
                        \XLite\View\Button\AButton::PARAM_LABEL => 'Update',
                        \XLite\View\Button\AButton::PARAM_STYLE => 'action',
                    )
                );
            } else {
                $result['approve'] = new \XLite\View\Button\Submit(
                    array(
                        \XLite\View\Button\AButton::PARAM_LABEL => 'Approve',
                        \XLite\View\Button\AButton::PARAM_STYLE => 'action',
                    )
                );
                $result['remove'] = new \XLite\View\Button\Submit(
                    array(
                        \XLite\View\Button\AButton::PARAM_LABEL => 'Remove',
                        \XLite\View\Button\AButton::PARAM_STYLE => 'action',
                    )
                );
            }

        } else {
            $result['submit'] = new \XLite\View\Button\Submit(
                array(
                    \XLite\View\Button\AButton::PARAM_LABEL => 'Create',
                    \XLite\View\Button\AButton::PARAM_STYLE => 'action',
                )
            );
        }

        return $result;
    }

    /**
     * Return whether review is approved
     *
     * @return boolean
     */
    protected function isApproved()
    {
        return \XLite\Module\XC\Reviews\Model\Review::STATUS_APPROVED == $this->getModelObject()->getStatus();
    }
}
