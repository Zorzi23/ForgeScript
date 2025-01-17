<?php
namespace ForgeScript\GrammarParser\Strategies;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use GrammiCore\GrammarParser\Tree\ArrayExpressionNode;
use GrammiCore\GrammarParser\Tree\LiteralNode;
use GrammiCore\GrammarParser\Tree\ObjectExpressionNode;
use GrammiCore\Lexer\LexerToken;

/**
 * Class ArrayExpressionParserStrategy
 * Parses array expressions
 */
class ArrayExpressionParserStrategy extends ParserStrategy {

    /**
     * Parse an array expression
     * 
     * @return ArrayExpressionNode
     */
    public function parse(): ArrayExpressionNode {
        $this->getParser()->expect('OPEN_ARRAY');
        $aElements = [];
        $iElementIndex = 0;
        $oCurrentToken = $this->getParser()->peek();
        while ($oCurrentToken->getType() !== 'CLOSE_ARRAY') {
            $oKeyNode = $this->parseKeyNode($oCurrentToken, $iElementIndex);
            $oValueNode = $this->getParser()->parseStatement();
            $oElement = new ObjectExpressionNode([
                'key' => $oKeyNode,
                'value' => $oValueNode,
            ], $oKeyNode);
            $aElements[] = $oElement;
            $oCurrentToken = $this->getParser()->peek();
            if ($oCurrentToken->getType() === 'ARRAY_ITEM_SEPARATOR') {
                $this->getParser()->next();
            }
            $oCurrentToken = $this->getParser()->peek();
        }
        $this->getParser()->expect('CLOSE_ARRAY');
        return new ArrayExpressionNode($aElements, $oCurrentToken);
    }

    /**
     * Parse the key node for an array element
     * 
     * @param LexerToken $oCurrentToken
     * @param int $iElementIndex
     * @return LiteralNode
     */
    private function parseKeyNode(LexerToken $oCurrentToken, int &$iElementIndex): LiteralNode {
        $oKeyNode = null;
        if ($this->getParser()->peekNext()->isType('ARRAY_ASSIGN')) {
            $this->getParser()->next();
            $oCurrentToken = $this->getParser()->peek();
        }
        if ($oCurrentToken->isType('ARRAY_ASSIGN')) {
            $this->getParser()->previous();
            $oMemberExpression = (new MemberExpressionParserStrategy())->setParser($this->getParser());
            $oKeyNode = $oMemberExpression->expectLiteralIdentifierTypes();
            $this->getParser()->next();
            return $oKeyNode;
        }
        $oKeyNode = new LiteralNode('INTEGER', $iElementIndex);
        $iElementIndex++;
        return $oKeyNode;
    }
}