<?php
namespace ForgeScript\GrammarParser\Strategies;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use GrammiCore\GrammarParser\Tree\FunctionDeclarationNode;
use GrammiCore\GrammarParser\Tree\IdentifierNode;

/**
 * Class FunctionDeclarationParserStrategy
 * Parses function declarations
 */
class FunctionDeclarationParserStrategy extends ParserStrategy {

    /**
     * Parse a function declaration
     * 
     * @return FunctionDeclarationNode
     */
    public function parse(): FunctionDeclarationNode {
        $this->getParser()->expect('FUNCTION_KEYWORD');
        return $this->parseFunction();
    }

    /**
     * Parse the function details
     * 
     * @return FunctionDeclarationNode
     */
    public function parseFunction(): FunctionDeclarationNode {
        $oId = $this->getParser()->expect('IDENTIFIER');
        $oIdentifier = new IdentifierNode($oId->getValue(), 'FUNCTION');
        $this->getParser()->expect('PRECEDENCE_BLOCK_START');
        $aParams = $this->parseFunctionParams();
        $this->getParser()->expect('PRECEDENCE_BLOCK_END');
        $oBody = BlockStatementParserStrategy::parser($this->getParser())->parse();
        return new FunctionDeclarationNode($oIdentifier, $aParams, $oBody);
    }

    /**
     * Parse the function parameters
     * 
     * @return array
     */
    private function parseFunctionParams(): array {
        $aParams = [];
        while ($this->getParser()->peek()->getType() !== 'PRECEDENCE_BLOCK_END') {
            $oParamId = $this->getParser()->expect('IDENTIFIER');
            $aParams[] = new IdentifierNode($oParamId->getValue(), 'FUNCTION_PARAM');
            if ($this->getParser()->peek()->getType() === 'ARRAY_ITEM_SEPARATOR') {
                $this->getParser()->next();
            }
        }
        return $aParams;
    }
}
