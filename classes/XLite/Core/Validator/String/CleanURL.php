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

namespace XLite\Core\Validator\String;

/**
 * CleanURL 
 */
class CleanURL extends \XLite\Core\Validator\String\RegExp
{
    /**
     * Class name
     *
     * @var string
     */
    protected $class;

    /**
     * Entity id
     *
     * @var mixed
     */
    protected $id;

    /**
     * Constructor
     *
     * @param boolean $nonEmpty Non-empty flag OPTIONAL
     * @param string  $regExp   Regular expression OPTIONAL
     *
     * @return void
     */
    public function __construct($nonEmpty = false, $regExp = null, $class = '', $id = null)
    {
        parent::__construct($nonEmpty, $this->getCleanURLPattern());

        if (empty($class)) {
            \Includes\ErrorHandler::fireError(
                static::t('Empty "class" parameter is passed to the {{method}}', array('method' => __METHOD__))
            );

        } else {
            $this->class = $class;
            $this->id    = $id;
        }
    }

    /**
     * Validate
     *
     * @param mixed $data Data
     *
     * @return void
     * @throws \XLite\Core\Validator\Exception
     */
    public function validate($data)
    {
        $data = $this->sanitize($data);

        if (!\XLite\Core\Converter::isEmptyString($data)) {
            parent::validate($data);

            $entity = \XLite\Core\Database::getRepo($this->class)->findOneByCleanURL($data);

            // DO NOT use "!==" here
            if ($entity && (empty($this->id) || $entity->getUniqueIdentifier() != $this->id)) {
                $this->throwCleanURLError();
            }
        }
    }

    /**
     * Sanitize
     *
     * @param mixed $data Data
     *
     * @return string
     */
    public function sanitize($data)
    {
        return substr(
            $data = preg_replace('/\.htm(l?)$/', '', $data),
            0,
            \XLite\Core\Database::getRepo($this->class)->getFieldInfo('cleanURL', 'length'));
    }

    /**
     * Clean URL pattern
     *
     * @return string
     */
    protected function getCleanURLPattern()
    {
        return '/^' . \XLite\Core\Converter::getCleanURLAllowedCharsPattern() . '$/S';
    }

    /**
     * Wrapper
     *
     * @return void
     * @throws \XLite\Core\Validator\Exception
     */
    protected function throwCleanURLError()
    {
        throw $this->throwError('Clean URL must be unique');
    }
}
