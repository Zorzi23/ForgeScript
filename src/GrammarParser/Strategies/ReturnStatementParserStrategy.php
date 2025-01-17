<?php
namespace ForgeScript\GrammarParser\Strategies;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use GrammiCore\GrammarParser\Tree\ReturnStatementNode;

class ReturnStatementParserStrategy extends ParserStrategy {

    /**
     * Parse a return statement
     * 
     * @return ReturnStatementNode
     */
    public function parse() {
        $this->getParser()->expect('RETURN_KEYWORD');
        $oArgument = $this->getParser()->parseStatement();
        if($this->getParser()->peek()->isType('VAR_DECLARATION_SEPARATOR')) {
            $this->getParser()->next();
        }
        return new ReturnStatementNode($oArgument);
    }

}
