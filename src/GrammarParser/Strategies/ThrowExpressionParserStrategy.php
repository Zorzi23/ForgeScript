<?php
namespace ForgeScript\GrammarParser\Strategies;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use GrammiCore\GrammarParser\Tree\ThrowStatementNode;

class ThrowExpressionParserStrategy extends ParserStrategy {

    /**
     * Parse a throw statement
     * 
     * @return ThrowStatementNode
     */
    public function parse() {
        $this->getParser()->expect('THROW_KEYWORD');
        $oArgument = $this->getParser()->parseStatement();
        if($this->getParser()->peek() && $this->getParser()->peek()->isType('VAR_DECLARATION_SEPARATOR')) {
            $this->getParser()->next();
        }
        return new ThrowStatementNode($oArgument);
    }

}
