<?php
namespace ForgeScript\GrammarParser\Strategies;
use Exception;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use GrammiCore\GrammarParser\Tree\AbstractSyntaxTreeNode;
use GrammiCore\GrammarParser\Tree\AssignmentExpressionNode;
use GrammiCore\GrammarParser\Tree\BinaryExpressionNode;
use GrammiCore\GrammarParser\Tree\IdentifierNode;
use GrammiCore\GrammarParser\Tree\LiteralNode;
use GrammiCore\GrammarParser\Tree\NewExpressionNode;
use GrammiCore\GrammarParser\Tree\SuperExpressionNode;
use GrammiCore\GrammarParser\Tree\ThisExpressionNode;
use GrammiCore\GrammarParser\Tree\UnaryExpressionNode;

class ExpressionStatementParserStrategy extends ParserStrategy {

    /**
     * Check if a token type is a binary operator
     *
     * @param string $sType
     * @return bool
     */
    public static function isBinaryOperator($sType): bool {
        return in_array($sType, static::binaryOperatorsTokenTypes());
    }

    /**
     * Check if a token type is a unary operator
     *
     * @param string $sType
     * @return bool
     */
    public static function isUnaryOperator($sType): bool {
        return in_array($sType, static::unaryOperatorsTokenTypes());
    }

    /**
     * Check if a token type is a unary logical operator
     *
     * @param string $sType
     * @return bool
     */
    public static function isUnaryLogicalOperator($sType): bool {
        return in_array($sType, array_filter(static::unaryOperatorsTokenTypes(), function($sUnary) {
            return strpos($sUnary, 'UR_LOGICAL') !== false;
        }));
    }

    /**
     * Check if a token type is a literal type
     *
     * @param string $sType
     * @return bool
     */
    public static function isLiteralTokenType($sType): bool {
        return in_array($sType, static::literalTypes());
    }

    /**
     *
     * @return string[]
     */
    public static function binaryOperatorsTokenTypes() {
        return [
            'AR_ADDITION',
            'AR_SUBTRACTION',
            'COND_EQUAL',
            'COND_NOT_EQUAL',
            'COND_GREATER',
            'COND_LESS',
            'COND_GREATER_OR_EQUAL',
            'COND_LESS_OR_EQUAL',
            'LOGICAL_AND',
            'LOGICAL_OR'
        ];
    }

    /**
     *
     * @return string[]
     */
    public static function unaryOperatorsTokenTypes() {
        return [
            'UR_ADDITION',
            'UR_SUBTRACTION',
            'UR_LOGICAL_NOT',
        ];
    }

    /**
     *
     * @return string[]
     */
    public static function literalTypes() {
        return [
            'INTEGER',
            'FLOAT',
            'STRING',
            'BOOLEAN',
            'NULL'
        ];
    }

    /**
     *
     * @return BinaryExpressionNode
     */
    public function parse() {
        return $this->parseBinaryExpression();
    }

    /**
     * Parse a binary expression
     *
     * @param int $iPrecedence
     * @return BinaryExpressionNode
     */
    private function parseBinaryExpression() {
        $oLeft = $this->parsePrimaryExpression();
        while ($this->getParser()->peek() && static::isBinaryOperator($this->getParser()->peek()->getType())) {
            $oOperator = $this->getParser()->next();
            $oRight = $this->parsePrimaryExpression();
            $oLeft = new BinaryExpressionNode($oOperator->getType(), $oLeft, $oRight);
        }
        return $oLeft;
    }

    /**
     * Parse a Assignment expression
     *
     * @param int $iPrecedence
     * @return AssignmentExpressionNode
     */
    private function parseAssignmentExpression() {
        $oIdentifier = $this->getParser()->peekPrevious();
        $oLeft = new IdentifierNode($oIdentifier->getValue(), 'VARIABLE');
        $this->getParser()->next();
        $oRight = $this->parse();
        if($this->getParser()->peek()->isType($sType = 'VAR_DECLARATION_SEPARATOR')) {
            $this->getParser()->expect($sType);
        }
        return new AssignmentExpressionNode($oLeft, $oRight);
    }

    /**
     * Parse a primary expression
     *
     * @return AbstractSyntaxTreeNode|null
     * @throws \Exception
     */
    private function parsePrimaryExpression() {
        $oPreviousToken = $this->getParser()->peekPrevious();
        $oToken = $this->getParser()->peek();
        if(!$oToken) { return null; }
        if($oToken->isType('ASSIGN') && $oPreviousToken->isType('IDENTIFIER')) {
            $oAssign = $this->parseAssignmentExpression();
            return $oAssign;
        }
        $aParserFunctions = static::relationBetweenTokenTypeParseMethod();
        if (static::isLiteralTokenType($sType = $oToken->getType())) {
            return $this->parseLiteral($sType);
        }
        if (static::isUnaryOperator($sType)) {
            return $this->parsePrimaryUnary($oToken);
        }
        if (isset($aParserFunctions[$oToken->getType()])) {
            return call_user_func([$this, $aParserFunctions[$oToken->getType()]]);
        }
        throw new Exception("Unexpected token: " . $oToken->getType());
    }

    private function parsePrimaryUnary($oToken) {
        $sOperator = $oToken->getType();
        $this->getParser()->next();
        $oToken = $this->getParser()->peek();
        if($oToken->isType("IDENTIFIER")) {
            return $this->parseUnary($sOperator, $oToken, true, true);
        }
        $this->getParser()->previousTimes(2);
        $oToken = $this->getParser()->peek();
        if($oToken->isType("IDENTIFIER")) {
            return $this->parseUnary($sOperator, $oToken, false);
        }
        if(static::isUnaryLogicalOperator($sOperator)) {
            $iLogicalUnary = 1;
            do {
                $this->getParser()->next();
                $oToken = $this->getParser()->peek();
                $iLogicalUnary++;
            }
            while(static::isUnaryLogicalOperator($oToken->getType()));
            $bLogicalFalseUnary = $iLogicalUnary % 2 !== 0;
            return $this->parseUnary($bLogicalFalseUnary ? 'UR_LOGICAL_NOT' : 'UR_LOGICAL_TRUE', $oToken, true, false);
        }

        throw new Exception("Invalid left-hand side expression in postfix operation");
    }

    private function parseUnary($sOperator, $oToken, $bPrefix, $bChangeIdentifierValue = false) {
        $this->getParser()->next();
        if($this->getParser()->peek()->isType('VAR_DECLARATION_SEPARATOR')) {
            $this->getParser()->next();
        }
        if(static::isUnaryOperator($this->getParser()->peek()->getType())) {
            $this->getParser()->next();
        }
        $oArgument = null;
        if(MemberExpressionParserStrategy::isMemberExpressionToken($this->getParser()->peek()->getType())) {
            $this->getParser()->previous();
            $oArgument = MemberExpressionParserStrategy::parser($this->getParser())->parse();
            $this->getParser()->next();
        }
        $oArgument = $oArgument ?: new IdentifierNode($oToken->getValue());
        return new UnaryExpressionNode($sOperator, $oArgument, $bPrefix, $bChangeIdentifierValue);
    }

    /**
     * Parse an identifier token
     *
     * @return AbstractSyntaxTreeNode
     */
    private function parseIdentifier() {
        $oNextPeek = $this->getParser()->peekNext();
        if($oNextPeek->isType('PRECEDENCE_BLOCK_START')) {
            return CallExpressionParserStrategy::parser($this->getParser())->parse();
        }
        if (static::isUnaryOperator($oNextPeek->getType())) {
            $this->getParser()->next();
            return $this->parsePrimaryUnary($oNextPeek);
        }
        if(MemberExpressionParserStrategy::isStartMemberExpressionToken($oNextPeek->getType())) {
            return MemberExpressionParserStrategy::parser($this->getParser())->parse();
        }
        if($oNextPeek->isType('ASSIGN')) {
            $this->getParser()->next();
            return $this->parseAssignmentExpression();
        }
        return new IdentifierNode($this->getParser()->next()->getValue());
    }

    /**
     * Parse a precedence block start token
     *
     * @return AbstractSyntaxTreeNode
     */
    private function parsePrecedenceBlockStart() {
        $this->getParser()->next();
        $oExpression = $this->parse();
        if($this->getParser()->peek()->isType('PRECEDENCE_BLOCK_END')) {
            $this->getParser()->expect('PRECEDENCE_BLOCK_END');
        }
        return $oExpression;
    }

    /**
     * Parse an open array token
     *
     * @return AbstractSyntaxTreeNode
     */
    private function parseOpenArray() {
        return ArrayExpressionParserStrategy::parser($this->getParser())->parse();
    }

    /**
     * Parse a this keyword token
     *
     * @return ThisExpressionNode
     */
    private function parseThisKeyword() {
        $this->getParser()->next();
        if($this->getParser()->peek()->isType('IDENTIFIER_OBJECT_SEPARATOR')) {
            $this->getParser()->previous();
            return MemberExpressionParserStrategy::parser($this->getParser())->parse();
        }
        return new ThisExpressionNode();
    }

    /**
     * Parse a super keyword token
     *
     * @return SuperExpressionNode
     */
    private function parseSuperKeyword() {
        $this->getParser()->next();
        if($this->getParser()->peek()->isType('IDENTIFIER_OBJECT_SEPARATOR')) {
            $this->getParser()->previous();
            return MemberExpressionParserStrategy::parser($this->getParser())->parse();
        }
        return new SuperExpressionNode();
    }

    /**
     * Parse a new expression
     *
     * @return NewExpressionNode
     */
    private function parseNewExpression(): NewExpressionNode {
        $this->getParser()->expect('NEW_KEYWORD');
        $oCallee = $this->parsePrimaryExpression();
        $this->getParser()->expect('PRECEDENCE_BLOCK_START');
        $aArgs = [];
        while ($this->getParser()->peek()->getType() !== 'PRECEDENCE_BLOCK_END') {
            $aArgs[] = $this->parse();
            if ($this->getParser()->peek()->getType() === 'ARRAY_ITEM_SEPARATOR') {
                $this->getParser()->next();
            }
        }
        $this->getParser()->expect('PRECEDENCE_BLOCK_END');
        return new NewExpressionNode($oCallee, $aArgs);
    }

    /**
     * Parse a literal value
     *
     * @param string $sType
     * @return LiteralNode
     */
    private function parseLiteral(string $sType): LiteralNode {
        $oToken = $this->getParser()->next();
        return new LiteralNode($sType, $oToken->getValue());
    }

    /**
     * 
     * @return array
     */
    private static function relationBetweenTokenTypeParseMethod() {
        return [
            'IDENTIFIER' => 'parseIdentifier',
            'PRECEDENCE_BLOCK_START' => 'parsePrecedenceBlockStart',
            'OPEN_ARRAY' => 'parseOpenArray',
            'THIS_KEYWORD' => 'parseThisKeyword',
            'SUPER_KEYWORD' => 'parseSuperKeyword',
            'NEW_KEYWORD' => 'parseNewExpression'
        ];
    }
}