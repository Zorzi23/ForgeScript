<?php
namespace ForgeScript\GrammarParser\Strategies;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use GrammiCore\GrammarParser\Tree\AssignmentExpressionNode;
use GrammiCore\GrammarParser\Tree\IdentifierNode;
use GrammiCore\GrammarParser\Tree\MemberExpressionNode;
use GrammiCore\GrammarParser\Tree\SuperExpressionNode;
use GrammiCore\GrammarParser\Tree\ThisExpressionNode;
use GrammiCore\Lexer\LexerToken;

/**
 * Class MemberExpressionParserStrategy
 * Parses member expressions
 */
class MemberExpressionParserStrategy extends ParserStrategy {

    /**
     * Check if a token type is a member expression token
     *
     * @param string $sType Token type
     * @return bool
     */
    public static function isMemberExpressionToken(string $sType): bool {
        return in_array($sType, static::memberExpressionTokens());
    }

    /**
     * Check if a token type is a start member expression token
     *
     * @param string $sType Token type
     * @return bool
     */
    public static function isStartMemberExpressionToken(string $sType): bool {
        return in_array($sType, array_filter(static::memberExpressionTokens(), function($sMemberType) {
            return strpos($sMemberType, 'CLOSE_') !== 0;
        }));
    }

    /**
     * Get the list of member expression tokens
     *
     * @return string[]
     */
    public static function memberExpressionTokens(): array {
        return [
            'OPEN_ARRAY',
            'CLOSE_ARRAY',
            'IDENTIFIER_OBJECT_SEPARATOR',
        ];
    }

    /**
     * Parse a member expression
     * 
     * @return MemberExpressionNode|AssignmentExpressionNode
     */
    public function parse() {
        $oId = $this->getParser()->expect(['IDENTIFIER', 'THIS_KEYWORD', 'SUPER_KEYWORD']);
        $oIdentifier = $this->createIdentifierNode($oId);
        $oMember = $this->expectMemberExpression();
        $oProperty = $this->expectLiteralIdentifierTypes();
        if ($oMember->isType('OPEN_ARRAY')) {
            $this->getParser()->expect('CLOSE_ARRAY');
        }
        $oRootMember = new MemberExpressionNode($oIdentifier, $oProperty);
        if (!$this->getParser()->peek()) {
            return $oRootMember;
        }
        if (!static::isStartMemberExpressionToken($this->getParser()->peek()->getType())) {
            return $this->handleAssignmentOrReturn($oRootMember);
        }
        return $this->parseMemberChain($oRootMember, $oMember);
    }

    /**
     * Create an identifier node based on the token type
     * 
     * @param LexerToken $oId
     * @return IdentifierNode|ThisExpressionNode|SuperExpressionNode
     */
    private function createIdentifierNode(LexerToken $oId) {
        if ($oId->isType('THIS_KEYWORD')) {
            return new ThisExpressionNode();
        } elseif ($oId->isType('SUPER_KEYWORD')) {
            return new SuperExpressionNode();
        } else {
            return new IdentifierNode($oId->getValue());
        }
    }

    /**
     * Handle assignment or return the root member expression
     * 
     * @param MemberExpressionNode $oRootMember
     * @return MemberExpressionNode|AssignmentExpressionNode
     */
    private function handleAssignmentOrReturn(MemberExpressionNode $oRootMember) {
        if ($this->getParser()->peek()->isType('ASSIGN')) {
            $this->getParser()->next();
            $oRight = $this->getParser()->parseStatement();
            $oAssign = new AssignmentExpressionNode($oRootMember, $oRight);

            if ($this->getParser()->peek()->isType('VAR_DECLARATION_SEPARATOR')) {
                $this->getParser()->next();
            }

            return $oAssign;
        }

        if ($this->getParser()->peek()->isType('VAR_DECLARATION_SEPARATOR')) {
            $this->getParser()->next();
        }

        return $oRootMember;
    }

    /**
     * Parse the member chain
     * 
     * @param MemberExpressionNode $oRootMember
     * @param LexerToken $oMember
     * @return MemberExpressionNode|AssignmentExpressionNode
     */
    private function parseMemberChain(MemberExpressionNode $oRootMember, LexerToken $oMember) {
        $oPreviousMember = $oRootMember;
        $oCurrentToken = $this->getParser()->peek();

        while (static::isMemberExpressionToken($oCurrentToken->getType())) {
            $this->getParser()->next();
            $oObjectProperty = $this->getParser()->parseStatement();
            $oNewMember = new MemberExpressionNode($oPreviousMember->getProperty('property'), $oObjectProperty);
            $oPreviousMember->setProperty('property', $oNewMember);
            $oPreviousMember = $oPreviousMember->getProperty('property');
            $oNext = $this->getParser()->peekNext();

            if (!$oNext || !static::isMemberExpressionToken($oNext->getType())) {
                break;
            }

            $this->getParser()->next();
            $oCurrentToken = $this->getParser()->peek();
        }

        if ($oMember->isType('OPEN_ARRAY')) {
            $this->getParser()->expect('CLOSE_ARRAY');
        }

        if ($this->getParser()->peek()->isType('ASSIGN')) {
            $this->getParser()->next();
            $oRight = $this->getParser()->parseStatement();
            $oAssign = new AssignmentExpressionNode($oRootMember, $oRight);
            if ($this->getParser()->peek()->isType('VAR_DECLARATION_SEPARATOR')) {
                $this->getParser()->next();
            }
            return $oAssign;
        }
        return $oRootMember;
    }

    /**
     * Expect a member expression token
     * 
     * @return LexerToken
     */
    private function expectMemberExpression(): LexerToken {
        $oCurrentToken = $this->getParser()->peek();
        if (!static::isMemberExpressionToken($oCurrentToken->getType())) {
            $this->getParser()->expectException('MemberExpressionToken', $oCurrentToken);
        }
        return $this->getParser()->next();
    }

    /**
     * Expect a literal or identifier token
     * 
     * @return mixed
     */
    public function expectLiteralIdentifierTypes() {
        $oCurrentToken = $this->getParser()->peek();
        $bLiteralIdentifier = $oCurrentToken->isType('IDENTIFIER') || ExpressionStatementParserStrategy::isLiteralTokenType($oCurrentToken->getType());
        if (!$bLiteralIdentifier) {
            $this->getParser()->expectException('Literal|Identifier', $oCurrentToken);
        }
        return $this->getParser()->parseStatement();
    }
}
