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

namespace XLite\Module\CDev\GoSocial\Logic\Import\Processor;

/**
 * Import products processor extension
 */
class Products extends \XLite\Logic\Import\Processor\Products implements \XLite\Base\IDecorator
{
    /**
     * Define columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = parent::defineColumns();

        $columns['useCustomOpenGraphMeta'] = array();
        $columns['openGraphMeta'] = array();

        return $columns;
    }

    /**
     * Get messages
     *
     * @return array
     */
    public static function getMessages()
    {
        return parent::getMessages()
            + array(
                'USER-USE-OG-META-FMT' => 'Wrong format of UseCustomOpenGraphMeta value',
            );
    }

    /**
     * Verify 'useCustomOpenGraphMeta' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyUseCustomOpenGraphMeta($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsBoolean($value)) {
            $this->addWarning('USER-USE-OG-META-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Import 'useCustomOpenGraphMeta' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param mixed                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importUseCustomOpenGraphMetaColumn(\XLite\Model\Product $model, $value, array $column)
    {
        $model->setUseCustomOG($this->normalizeValueAsBoolean($value));
    }

    /**
     * Import 'openGraphMeta' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param mixed                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importOpenGraphMetaColumn(\XLite\Model\Product $model, $value, array $column)
    {
        if (!$model->getUseCustomOG()) {
            $value = $model->getOpenGraphMetaTags(false);

        } elseif (is_array($value)) {
            $value = implode(PHP_EOL, $value);
        }

        $model->setOgMeta($value);
    }
}
