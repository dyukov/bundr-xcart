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

namespace XLite\View\Form\Product\Modify;

/**
 * Details
 */
class Single extends \XLite\View\Form\Product\Modify\Base\Single
{
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
     * Ability to add the 'enctype="multipart/form-data"' form attribute
     *
     * @return boolean
     */
    protected function isMultipart()
    {
        return true;
    }

    /**
     * Get validator
     *
     * @return \XLite\Core\Validator\HashArray
     */
    protected function getValidator()
    {
        $validator = parent::getValidator();

        $data = $validator->addPair('postedData', new \XLite\Core\Validator\HashArray());
        $this->setDataValidators($data);

        return $validator;
    }

    /**
     * Get product identificator from request
     *
     * @return integer Product identificator
     */
    protected function getProductId()
    {
        return \XLite\Core\Request::getInstance()->product_id ?: \XLite\Core\Request::getInstance()->id;
    }

    /**
     * Set validators pairs for products data
     *
     * @param mixed $data Data
     *
     * @return null
     */
    protected function setDataValidators(&$data)
    {
        $data->addPair('sku', new \XLite\Core\Validator\SKU($this->getProductId()), null, 'SKU');
        $data->addPair('name', new \XLite\Core\Validator\String(true), null, 'Product Name');
        $data->addPair('category_ids', new \XLite\Core\Validator\PlainArray(), \XLite\Core\Validator\Pair\APair::SOFT, 'Category')
            ->setValidator(new \XLite\Core\Validator\Integer());
        $data->addPair('price', new \XLite\Core\Validator\Float(), null, 'Price')->setRange(0);
        $data->addPair('weight', new \XLite\Core\Validator\Float(), null, 'Weight')->setRange(0);
        $data->addPair('shippable', new \XLite\Core\Validator\Enum\Boolean(), null, 'Shippable');
        $data->addPair('enabled', new \XLite\Core\Validator\Enum\Boolean(), null, 'Available for sale');
        $data->addPair('metaTitle', new \XLite\Core\Validator\String(), null, 'Product page title');
        $data->addPair('briefDescription', new \XLite\Core\Validator\String(), null, 'Brief description');
        $data->addPair('description', new \XLite\Core\Validator\String(), null, 'Full description');
        $data->addPair('metaTags', new \XLite\Core\Validator\String(), null, 'Meta keywords');
        $data->addPair('metaDesc', new \XLite\Core\Validator\String(), null, 'Meta description');

        $data->addPair(
            'cleanURL',
            new \XLite\Core\Validator\String\CleanURL(false, null, '\XLite\Model\Product', $this->getProductId()),
            null,
            'Clean URL'
        );

        $data->addPair(
            'memberships',
            new \XLite\Core\Validator\PlainArray(),
            \XLite\Core\Validator\Pair\APair::SOFT,
            'Membership'
        )->setValidator(new \XLite\Core\Validator\Integer());

        $data->addPair(
            'category_ids',
            new \XLite\Core\Validator\PlainArray(),
            \XLite\Core\Validator\Pair\APair::SOFT,
            'Category'
        )->setValidator(new \XLite\Core\Validator\Integer());
    }
}
