<?php
namespace ForgeScript\GrammarParser\Strategies;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use GrammiCore\GrammarParser\Tree\CallExpressionNode;
use GrammiCore\GrammarParser\Tree\IdentifierNode;
use GrammiCore\GrammarParser\Tree\VariableDeclarationNode;
use GrammiCore\GrammarParser\Tree\VariableDeclaratorNode;
use GrammiCore\Lexer\LexerToken;

/**
 * Class VariableDeclarationParserStrategy
 * Parses variable declarations
 */
class VariableDeclarationParserStrategy extends ParserStrategy {

    /**
     * Parse a variable declaration
     * 
     * @return VariableDeclarationNode
     */
    public function parse(): VariableDeclarationNode {
        $oVarKeyword = $this->getParser()->expect('VAR_KEYWORD');
        $oId = $this->getParser()->expect('IDENTIFIER');
        $oIdentifier = new IdentifierNode($oId->getValue(), 'VARIABLE');
        $aDeclarations = $this->parseDeclarations($oIdentifier, $oId);

        $oDeclaration = new VariableDeclarationNode($aDeclarations, $oVarKeyword->getValue());
        return $oDeclaration;
    }

    /**
     * Parse variable declarators
     * 
     * @param IdentifierNode $oIdentifier
     * @param LexerToken $oId
     * @return array
     */
    private function parseDeclarations(IdentifierNode $oIdentifier, LexerToken $oId): array {
        $aDeclarations = [];
        while (!$this->getParser()->peek()->isType('VAR_DECLARATION_SEPARATOR')) {
            $oCurrentToken = $this->getParser()->peek();
            $xInit = $this->parseInitializer($oCurrentToken);
            if (!$xInit) {
                break;
            }
            $oDeclarator = new VariableDeclaratorNode($oIdentifier, $xInit);
            $aDeclarations[] = $oDeclarator;
            if ($xInit instanceof CallExpressionNode) {
                break;
            }
            $oCurrentToken = $this->getParser()->peek();
            if (!$oCurrentToken || $oCurrentToken->isType('VAR_DECLARATION_SEPARATOR')) {
                $this->getParser()->next();
                break;
            }
        }

        return $aDeclarations;
    }

    /**
     * Parse the initializer for a variable declarator
     * 
     * @param LexerToken $oCurrentToken
     * @return mixed
     */
    private function parseInitializer(LexerToken $oCurrentToken) {
        if ($oCurrentToken->isType('ASSIGN')) {
            $this->getParser()->next();
            return $this->getParser()->parseStatement();
        }
        return null;
    }
}
