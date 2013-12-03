<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 03/12/2013
 * Time: 11:44
 */

namespace AdEntify\CoreBundle\Dql;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

class RegExp extends FunctionNode
{
    public $firstRegExExpression = null;
    public $secondRegExExpression = null;
    /**
     * Parse the query expression
     *
     * @param \Doctrine\ORM\Query\Parser $parser
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstRegExExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->secondRegExExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
    /**
     * Return the created string representation
     *
     * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
     *
     * @return string
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return '(SELECT '.$this->firstRegExExpression->dispatch($sqlWalker).' REGEXP '.$this->secondRegExExpression->dispatch($sqlWalker).')';
    }
}