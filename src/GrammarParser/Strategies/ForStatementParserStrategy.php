<?php
namespace ForgeScript\GrammarParser\Strategies;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use ForgeScript\GrammarParser\Tree\CallExpressionNode;
use ForgeScript\GrammarParser\Tree\ForStatementNode;

/**
 * Class ForStatementParserStrategy
 * Parses for statements
 */
class ForStatementParserStrategy extends ParserStrategy {

    /**
     * Parse a for statement
     * 
     * @return ForStatementNode
     */
    public function parse(): ForStatementNode {
        $this->getParser()->expect('FOR_KEYWORD');
        $this->getParser()->expect('PRECEDENCE_BLOCK_START');
        $oInit = VariableDeclarationParserStrategy::parser($this->getParser())->parse();
        $oTest = ExpressionStatementParserStrategy::parser($this->getParser())->parse();
        $oRight = $oTest->getProperty('right');
        if (!($oRight instanceof CallExpressionNode)) {
            $this->getParser()->expect('VAR_DECLARATION_SEPARATOR');
        }
        $oUpdate = ExpressionStatementParserStrategy::parser($this->getParser())->parse();
        $this->getParser()->expect('PRECEDENCE_BLOCK_END');
        $oBody = BlockStatementParserStrategy::parser($this->getParser())->parse();

        return new ForStatementNode($oInit, $oTest, $oUpdate, $oBody);
    }
}
