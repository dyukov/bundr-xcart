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

namespace XLite\View\Form\Category\Modify;

/**
 * Single
 */
class Single extends \XLite\View\Form\Category\Modify\AModify
{
    /**
     * getDefaultTarget
     *
     * @return string
     */
    protected function getDefaultTarget()
    {
        return 'category';
    }

    /**
     * getDefaultAction
     *
     * @return string
     */
    protected function getDefaultAction()
    {
        return 'modify';
    }

    /**
     * getDefaultParams
     *
     * @return array
     */
    protected function getDefaultParams()
    {
        $list = parent::getDefaultParams();
        $list['category_id'] = $this->getCategoryId();
        $list['parent_id']   = $this->getParentCategoryId();

        return $list;
    }

    /**
     * Get validator
     *
     * @return \XLite\Core\Validator\HashArray
     */
    protected function getValidator()
    {
        $validator = parent::getValidator();
        $this->setDataValidators($validator->addPair('postedData', new \XLite\Core\Validator\HashArray()));

        return $validator;
    }

    /**
     * Set validators pairs for products data
     *
     * @param mixed $data Data
     *
     * @return null
     */
    protected function setDataValidators($data)
    {
        $data->addPair('name', new \XLite\Core\Validator\String(true), null, 'Category name');
        $data->addPair('show_title', new \XLite\Core\Validator\Enum\Boolean(), null, 'Category title');
        $data->addPair('description', new \XLite\Core\Validator\String(), null, 'Description');
        $data->addPair('enabled', new \XLite\Core\Validator\Enum\Boolean(), null, 'Availability');
        $data->addPair('metaTitle', new \XLite\Core\Validator\String(), null, 'Meta title');
        $data->addPair('metTags', new \XLite\Core\Validator\String(), null, 'Meta keywords');
        $data->addPair('metaDesc', new \XLite\Core\Validator\String(), null, 'Meta description');

        $data->addPair(
            'membership_ids',
            new \XLite\Core\Validator\PlainArray(),
            \XLite\Core\Validator\Pair\APair::SOFT,
            'Membership'
        )->setValidator(new \XLite\Core\Validator\Integer());

        $data->addPair(
            'cleanURL',
            new \XLite\Core\Validator\String\CleanURL(false, null, '\XLite\Model\Category', $this->getCategoryId()),
            null,
            'Clean URL'
        );
    }
}
