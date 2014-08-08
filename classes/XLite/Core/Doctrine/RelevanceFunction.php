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

namespace XLite\Core\Doctrine;

/**
 * RELEVANCE(search_words, title_field_name, text_field_name)
 *
 * IF(condition, then, else) MySQL function realisation
 */
class RelevanceFunction extends \Doctrine\ORM\Query\AST\Functions\FunctionNode
{
    /**
     * Parse function
     *
     * @param \Doctrine\ORM\Query\Parser $parser Parser
     *
     * @return void
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(\Doctrine\ORM\Query\Lexer::T_IDENTIFIER);
        $parser->match(\Doctrine\ORM\Query\Lexer::T_OPEN_PARENTHESIS);

        $this->relevanceSearchWords = $parser->Literal();
        $parser->match(\Doctrine\ORM\Query\Lexer::T_COMMA);

        $this->relevanceTitleField = $parser->SingleValuedPathExpression();
        $parser->match(\Doctrine\ORM\Query\Lexer::T_COMMA);

        $this->relevanceTextField = $parser->SingleValuedPathExpression();
        $parser->match(\Doctrine\ORM\Query\Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Get SQL query part
     *
     * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker SQL walker
     *
     * @return string
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        $sql = '';
        $phrase = trim($this->relevanceSearchWords->dispatch($sqlWalker), '\'');
        $words = explode(' ', $phrase);
        $coeffTitle = round((20 / count($words)), 2);
        $coeffText  = round((10 / count($words)), 2);

        $sql .= '( IF (' . $this->relevanceTitleField->dispatch($sqlWalker) . ' LIKE \'%' . $phrase . '%\', 60, 0)';
        $sql .= '+ IF (' . $this->relevanceTextField->dispatch($sqlWalker) . ' LIKE \'%' . $phrase . '%\', 10, 0)';
        foreach($words as $word) {
            $sql .= '+ IF (' . $this->relevanceTitleField->dispatch($sqlWalker) . ' LIKE \'%' . $word . '%\', ' . $coeffTitle . ', 0)';
            $sql .= '+ IF (' . $this->relevanceTextField->dispatch($sqlWalker) . ' LIKE \'%' . $word . '%\', ' . $coeffText . ', 0)';
        }
        $sql .= ')';

        return $sql;
    }

}

