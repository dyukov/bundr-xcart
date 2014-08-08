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

namespace XLite\Module\CDev\Wholesale\Controller\Admin;

/**
 * Wholesale pricing page controller (Product modify section)
 */
class Product extends \XLite\Controller\Admin\Product implements \XLite\Base\IDecorator
{
    /**
     * Page key
     */
    const PAGE_WHOLESALE_PRICING = 'wholesale_pricing';


    /**
     * Get pages
     *
     * @return array
     */
    public function getPages()
    {
        $list = parent::getPages();

        if (!$this->isNew()) {
            $list[static::PAGE_WHOLESALE_PRICING] = static::t('Wholesale pricing');
        }

        return $list;
    }

    /**
     * Return page template
     *
     * @return string
     */
    public function getPageTemplate()
    {
        return defined('static::PAGE_VARIANTS')
            && static::PAGE_VARIANTS == $this->getPage()
            && \XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\WholesalePrice')->hasWholesalePrice($this->getProduct())
            ? 'modules/CDev/Wholesale/variants/body.tpl'
            : parent::getPageTemplate();
    }

    /**
     * Get pages templates
     *
     * @return array
     */
    protected function getPageTemplates()
    {
        $list = parent::getPageTemplates();

        if (!$this->isNew()) {
            $list[static::PAGE_WHOLESALE_PRICING] = 'modules/CDev/Wholesale/pricing/body.tpl';
        }

        return $list;
    }

    /**
     * Get minimum quantities for every membership
     *
     * @return array
     */
    public function getMinQuantities()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\MinQuantity')
            ->getAllMinQuantities($this->getProduct());
    }

    /**
     * Check if wholesale prices enabled for current product
     *
     * @return boolean
     */
    public function isWholesalePricesEnabled()
    {
        return $this->getProduct()->isWholesalePricesEnabled();
    }

    /**
     * Build minimum quantity info structure to update in DB.
     *
     * @return array
     */
    protected function getMinQuantitiesInsertInfo()
    {
        $result = array();
        $product = $this->getProduct();

        foreach ($this->getPostedData('minQuantity') as $id => $data) {

            $rate = array(
                'quantity' => max(1, intval($data)),
                'product'  => $product,
            );

            $membership = \XLite\Core\Database::getRepo('XLite\Model\Membership')
                ->findOneBy(array('membership_id' => $id));

            if (isset($membership)) {
                $rate['membership'] = $membership;
            }

            $result[] = $rate;
        }

        return $result;
    }

    /**
     * doActionMinQuantitiesUpdate
     *
     * @return void
     */
    protected function doActionMinQuantitiesUpdate()
    {
        \XLite\Core\Database::getRepo('\XLite\Module\CDev\Wholesale\Model\MinQuantity')
            ->deleteByProduct($this->getProduct());

        \XLite\Core\Database::getRepo('\XLite\Module\CDev\Wholesale\Model\MinQuantity')
            ->insertInBatch($this->getMinQuantitiesInsertInfo());
    }

    /**
     * Update list
     *
     * @return void
     */
    protected function doActionWholesalePricesUpdate()
    {
        $list = new \XLite\Module\CDev\Wholesale\View\ItemsList\WholesalePrices();
        $list->processQuick();

        // Additional correction to re-define end of subtotal range for each discount
        \XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\WholesalePrice')
            ->correctQuantityRangeEnd($this->getProduct());

        // Update default product price (for 1 item and all customers)
        \XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\WholesalePrice')
            ->updateDefaultProductPrice($this->getProduct());
    }

    /**
     * Extend doActionModify - default wholesale price updating added
     *
     * @return void
     */
    protected function doActionModify()
    {
        parent::doActionModify();

        // Update default wholesale price (for 1 item and all customers)
        \XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\WholesalePrice')
            ->updateDefaultWholesalePrice($this->getProduct());
    }
}
