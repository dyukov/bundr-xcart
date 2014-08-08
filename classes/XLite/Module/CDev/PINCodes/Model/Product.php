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
 * Product
 *
 * @HasLifecycleCallbacks
 */
class Product extends \XLite\Model\Product implements \XLite\Base\IDecorator
{
    /**
     * Whether pin codes are enabled for this product
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $pinCodesEnabled = false;

    /**
     * Whether to create pin codes automatically
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $autoPinCodes = false;

    /**
     * Pin codes (relation)
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @OneToMany (targetEntity="XLite\Module\CDev\PINCodes\Model\PinCode", mappedBy="product")
     */
    protected $pinCodes;

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->pinCodes = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Returns true if product has pin codes enabled and pin code autogeneration is turned off
     *
     * @return boolean 
     */
    public function hasManualPinCodes()
    {
        return $this->getPinCodesEnabled() && !$this->getAutoPinCodes();
    }

    /**
     * Returns sold pins count 
     *
     * @return integer
     */
    public function getSoldPinCodesCount()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\CDev\PINCodes\Model\PinCode')->countSold($this);
    }

    /**
     * Returns remaining pins count 
     *
     * @return integer
     */
    public function getRemainingPinCodesCount()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\CDev\PINCodes\Model\PinCode')->countRemaining($this);
    }

    /**
     * @PreRemove
     */
    public function prepareBeforeRemove()
    {
        parent::prepareBeforeRemove();

        foreach ($this->getPinCodes() as $code) {
            if (!$code->getOrderItem()) {
                \XLite\Core\Database::getEM()->remove($code);
            }
        }
    }
}
