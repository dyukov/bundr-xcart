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

namespace XLite\Module\XC\WebmasterKit\View;

/**
 * Abstract widget
 */
abstract class AView extends \XLite\View\AView implements \XLite\Base\IDecorator
{
    /**
     * Profiler data
     *
     * @var array
     */
    protected static $profilerInfo;

    /**
     * So called "static constructor".
     * NOTE: do not call the "parent::__constructStatic()" explicitly: it will be called automatically
     *
     * @return void
     */
    public static function __constructStatic()
    {
        parent::__constructStatic();

        static::$profilerInfo = array(
            'isEnabled'     => \XLite\Module\XC\WebmasterKit\Core\Profiler::isTemplatesProfilingEnabled(),
            'markTemplates' => \XLite\Module\XC\WebmasterKit\Core\Profiler::markTemplatesEnabled(),
            'countDeep'     => 0,
            'countLevel'    => 0,
        );
    }

    /**
     * Via this method the widget registers the CSS files which it uses.
     * During the viewers initialization the CSS files are collecting into the static storage.
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        if (static::$profilerInfo['markTemplates']) {
            $list[] = 'modules/XC/WebmasterKit/template_debuger.css';
        }

        return $list;
    }

    /**
     * Get JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'modules/XC/WebmasterKit/core.js';

        return $list;
    }

    /**
     * Prepare template display
     *
     * @param string $template Template short path
     *
     * @return array
     */
    protected function prepareTemplateDisplay($template)
    {
        $result = parent::prepareTemplateDisplay($template);
        $cnt = static::$profilerInfo['countDeep']++;
        $cntLevel = static::$profilerInfo['countLevel']++;

        if (static::$profilerInfo['isEnabled']) {
            $timePoint = str_repeat('+', $cntLevel) . '[TPL ' . str_repeat('0', 4 - strlen((string)$cnt)) . $cnt . '] '
                . get_class($this) . ' :: ' . substr($template, strlen(LC_DIR_SKINS));
                \XLite\Module\XC\WebmasterKit\Core\Profiler::getInstance()->log($timePoint);
            $result['timePoint'] = $timePoint;
        }

        if (static::$profilerInfo['markTemplates']) {
            $template = substr($template, strlen(LC_DIR_SKINS));
            $markTplText = get_class($this) . ' : ' . $template . ' (' . $cnt . ')'
                . ($this->viewListName ? ' [\'' . $this->viewListName . '\' list child]' : '');

            echo ('<!-- ' . $markTplText . ' {' . '{{ -->');
            $result['markTplText'] = $markTplText;
        }

        return $result;
    }

    /**
     * Finalize template display
     *
     * @param string $template     Template short path
     * @param array  $profilerData Profiler data which is calculated and returned in the 'prepareTemplateDisplay' method
     *
     * @return void
     */
    protected function finalizeTemplateDisplay($template, array $profilerData)
    {
        if (1 < func_num_args()) {
            $profilerData = func_get_arg(1);

        } else {
            $profilerData = static::$profilerInfo;
        }

        if (isset($profilerData['markTplText'])) {
            echo ('<!-- }}' . '} ' . $profilerData['markTplText'] . ' -->');
        }

        if (isset($profilerData['timePoint'])) {
            \XLite\Module\XC\WebmasterKit\Core\Profiler::getInstance()->log($profilerData['timePoint']);
        }

        static::$profilerInfo['countLevel']--;

        parent::finalizeTemplateDisplay($template, $profilerData);
    }

    /**
     * Register files from common repository
     *
     * @return array
     */
    protected function getCommonFiles()
    {
        $list = parent::getCommonFiles();

        if (static::$profilerInfo['markTemplates']) {
            $list[static::RESOURCE_JS][]  = 'modules/XC/WebmasterKit/template_debuger.js';
        }

        return $list;
    }

    // {{{ Helpers

    /**
     * Get current widget class name
     *
     * @return string
     */
    protected function getCurrentClassName()
    {
        return get_called_class();
    }

    /**
     * varDump flexy modifier
     *
     * @param mixed $value Value
     *
     * @return string
     */
    protected function flexyModifierVarDump($value)
    {
        return var_export($value, true);
    }

    // }}}
}
