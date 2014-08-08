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

namespace XLite\Module\CDev\PINCodes\Model;

/**
 * PIN Code 
 *
 * @Entity (repositoryClass="\XLite\Module\CDev\PINCodes\Model\Repo\PinCode")
 * @Table  (
 *      name="pin_codes",
 *      uniqueConstraints={
 *          @UniqueConstraint (name="productCode", columns={"code", "productId"})
 *      }
 * )
 * @HasLifecycleCallbacks
 */
class PinCode extends \XLite\Model\AEntity
{
    /**
     * PIN Code unique id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer")
     */
    protected $id;

    /**
     * Code
     *
     * @var string
     *
     * @Column (type="string", length=64)
     */
    protected $code = '';

    /**
     * Create date
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $createDate;

    /**
     * Is sold
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $isSold = false;

    /**
     * Product (relation)
     *
     * @var \XLite\Model\Product
     *
     * @ManyToOne  (targetEntity="XLite\Model\Product", inversedBy="pinCodes")
     * @JoinColumn (name="productId", referencedColumnName="product_id", onDelete="SET NULL")
     */
    protected $product;

    /**
     * OrderItem (relation)
     *
     * @var \XLite\Model\OrderItem
     *
     * @ManyToOne  (targetEntity="XLite\Model\OrderItem", inversedBy="pinCodes")
     * @JoinColumn (name="orderItemId", referencedColumnName="item_id", onDelete="SET NULL")
     */
    protected $orderItem;

    
    /**
     * Generate pin code 
     *
     * @return string
     * 
     * @throws \Exception on attempt to autogenerate without $product defined
     */
    public function generateCode()
    {
        if (!$this->getProduct()) {
            throw new \Exception('Can not ensure pin uniqueness without a product assigned to this pin code');
        }
    
        $newValue = null;
        $repo = \XLite\Core\Database::getRepo('XLite\Module\CDev\PINCodes\Model\PinCode');
        while (!$newValue || $repo->findOneBy(array('code' => $newValue, 'product' => $this->getProduct()->getId()))) {
            $newValue = $this->getRandomCode();
        }
        $this->code = $newValue;

        return $this->code;
    }

    /**
     * Prepare creation date 
     * 
     * @return void
     *
     * @PrePersist
     */
    public function prepareDate()
    {
        if (!$this->getCreateDate()) {
            $this->setCreateDate(\XLite\Core\Converter::time());
        }
    }

    /**
     * Return false if not sold, OrderItem if sold and order exists, true otherwise 
     * 
     * @return \XLite\Model\OrderItem|boolean
     */
    public function getStatusData()
    {
        $return = false;
        if ($this->isSold) {
            if (!$this->getOrderItem()) {
                $return = true;
            } else {
                $return = $this->getOrderItem();
            }
        }

        return $return;
    }

    /**
     * Generates random pin code 
     * 
     * @return string
     */
    protected function getRandomCode()
    {
        return sprintf('%04d%04d%04d%04d', rand(0, 9999), rand(0, 9999), rand(0, 9999), rand(0, 9999));
    }

}
