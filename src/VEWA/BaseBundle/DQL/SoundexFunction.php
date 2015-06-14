<?php

use Doctrine\ORM\Query\AST\Functions\FunctionNode;

class SoundexFunction extends FunctionNode
{
    public $stringExpression = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->$stringExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);

    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'SOUNDEX(' . $this->$stringExpression->dispatch($sqlWalker) . ')';
    }
}
