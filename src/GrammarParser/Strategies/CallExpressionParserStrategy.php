<?php
namespace ForgeScript\GrammarParser\Strategies;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use GrammiCore\GrammarParser\Tree\CallExpressionNode;
use GrammiCore\GrammarParser\Tree\IdentifierNode;

/**
 * Class CallExpressionParserStrategy
 * Parses function call statements
 */
class CallExpressionParserStrategy extends ParserStrategy {

    /**
     * Parse a function call statement
     * 
     * @return CallExpressionNode
     */
    public function parse(): CallExpressionNode {
        $aArguments = [];
        $oIdentifier = new IdentifierNode($this->getParser()->next()->getValue());
        $this->getParser()->expect('PRECEDENCE_BLOCK_START');
        while (!$this->getParser()->peek()->isType('PRECEDENCE_BLOCK_END')) {
            $aArguments[] = $this->getParser()->parseStatement();
            $oCurrentToken = $this->getParser()->next();
            if (!$oCurrentToken || $oCurrentToken->isType('PRECEDENCE_BLOCK_END')) {
                break;
            }
        }
        $this->finalizeCallExpression();
        return new CallExpressionNode($oIdentifier, $aArguments);
    }

    /**
     * Finalize the parsing of a call expression
     */
    private function finalizeCallExpression(): void {
        if ($this->getParser()->peek() && $this->getParser()->peek()->isType('PRECEDENCE_BLOCK_END')) {
            $this->getParser()->next();
        }
        if ($this->getParser()->peek() && $this->getParser()->peek()->isType('VAR_DECLARATION_SEPARATOR')) {
            $this->getParser()->expect('VAR_DECLARATION_SEPARATOR');
        }
    }
}
