<?php
namespace ForgeScript\GrammarParser\Strategies;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use GrammiCore\GrammarParser\Tree\ConditionStatementNode;

class ConditionStatementParserStrategy extends ParserStrategy {

    /**
     * Parse a condition statement
     * 
     * @return ConditionStatementNode
     */
    public function parse() {
        $this->getParser()->expect(['IF_KEYWORD', 'ELSE_IF_KEYWORD']);
        $oTest = ExpressionStatementParserStrategy::parser($this->getParser())->parse();
        $oConsequent = BlockStatementParserStrategy::parser($this->getParser())->parse();
        $oAlternate = null;
        if ($this->getParser()->peek() && in_array($this->getParser()->peek()->getType(), ['ELSE_KEYWORD', 'ELSE_IF_KEYWORD'])) {
            if($this->getParser()->peek()->isType('ELSE_IF_KEYWORD')) {
                return $this->parse();
            }
            $this->getParser()->next();
            if($this->getParser()->peek()->isType('IF_KEYWORD')) {
                return $this->parse();
            }
            $oAlternate = $this->getParser()->parseStatement();
        }
        return new ConditionStatementNode($oTest, $oConsequent, $oAlternate);
    }

}
