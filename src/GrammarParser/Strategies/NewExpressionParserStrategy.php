<?php
namespace ForgeScript\GrammarParser\Strategies;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use GrammiCore\GrammarParser\Tree\NewExpressionNode;

class NewExpressionParserStrategy extends ParserStrategy {

    /**
     * Parse a new expression
     * 
     * @return NewExpressionNode
     */
    public function parse() {
        $this->getParser()->expect('NEW_KEYWORD');
        $oCallExpression = CallExpressionParserStrategy::parser($this->getParser())->parse();
        $oClassId = $oCallExpression->getProperty('callee');
        $aArguments = $oCallExpression->getProperty('arguments');
        return new NewExpressionNode($oClassId, $aArguments);
    }

}
