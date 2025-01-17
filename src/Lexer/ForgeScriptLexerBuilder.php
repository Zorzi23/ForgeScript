<?php
namespace ForgeScript\Lexer;
use GrammiCore\Lexer\LexerAnalyzer;
use GrammiCore\Lexer\LexerRule;

class ForgeScriptLexerBuilder {

    public static function build() {
        $oLexer = new LexerAnalyzer();
        $oLexer->addRule(LexerRule::patternTypeName('\s+', 'WHITESPACE', 'WHITESPACE')->setSkip(true));
        $oLexer->addRule(LexerRule::patternTypeName('\/\/', 'LINE_COMMENT', 'COMMENT')->setSkip(true));
        $oLexer->addRule(LexerRule::patternTypeName('##', 'MULTI_LINE_COMMENT_START', 'COMMENT')->setSkip(true));
        $oLexer->addRule(LexerRule::patternTypeName('#', 'MULTI_LINE_COMMENT_END', 'COMMENT')->setSkip(true));
        $oLexer->addRule(LexerRule::patternTypeName('=>', 'ARRAY_ASSIGN', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('var', 'VAR_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('let', 'VAR_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('property', 'PROPERTY_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('prop', 'PROPERTY_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('static', 'STATIC_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('public', 'PUBLIC_MODIFIER_KEYWORD', 'MODIFIER_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('private', 'PRIVATE_MODIFIER_KEYWORD', 'MODIFIER_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('protected', 'PROTECTED_MODIFIER_KEYWORD', 'MODIFIER_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('function', 'FUNCTION_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('return', 'RETURN_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('class', 'CLASS_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('extends', 'EXTENDS_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('method', 'METHOD_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('throw', 'THROW_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('if', 'IF_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('else', 'ELSE_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('elif', 'ELSE_IF_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('for', 'FOR_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('try', 'TRY_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('catch', 'CATCH_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('finally', 'FINALLY_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('this', 'THIS_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('super', 'SUPER_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('new', 'NEW_KEYWORD', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('===', 'COND_EQUAL_TYPE', 'LOGICAL_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('==', 'COND_EQUAL', 'LOGICAL_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('!==', 'COND_NOT_EQUAL_TYPE', 'LOGICAL_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('!=', 'COND_NOT_EQUAL', 'LOGICAL_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('>=', 'COND_GREATER_OR_EQUAL', 'LOGICAL_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('<=', 'COND_LESS_OR_EQUAL', 'LOGICAL_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('>', 'COND_GREATER', 'LOGICAL_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('<', 'COND_LESS', 'LOGICAL_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('XOR', 'LOGICAL_XOR', 'LOGICAL_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('&&|AND', 'LOGICAL_AND', 'LOGICAL_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\\\\', 'LOGICAL_OR', 'LOGICAL_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('null', 'NULL', 'VALUE'));
        $oLexer->addRule(LexerRule::patternTypeName('true|false', 'BOOLEAN', 'VALUE'));
        $oLexer->addRule(LexerRule::patternTypeName('[0-9]+', 'INTEGER', 'VALUE'));
        $oLexer->addRule(LexerRule::patternTypeName('[0-9]*\.[0-9]+', 'FLOAT', 'VALUE'));
        $oLexer->addRule(LexerRule::patternTypeName('\'[^\']*\'', 'STRING', 'VALUE'));
        $oLexer->addRule(LexerRule::patternTypeName('"[^"]*"', 'STRING', 'VALUE'));
        $oLexer->addRule(LexerRule::patternTypeName('[a-zA-Z_][a-zA-Z0-9_]*', 'IDENTIFIER', 'IDENTIFIER_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\.', 'IDENTIFIER_OBJECT_SEPARATOR', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\(', 'PRECEDENCE_BLOCK_START', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\)', 'PRECEDENCE_BLOCK_END', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\{', 'BLOCK_START', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\}', 'BLOCK_END', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\[', 'OPEN_ARRAY', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\]', 'CLOSE_ARRAY', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName(';', 'VAR_DECLARATION_SEPARATOR', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName(',', 'ARRAY_ITEM_SEPARATOR', 'KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\+\+', 'UR_ADDITION', 'UR_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\-\-', 'UR_SUBTRACTION', 'UR_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\!', 'UR_LOGICAL_NOT', 'UR_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\+', 'AR_ADDITION', 'AR_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\-', 'AR_SUBTRACTION', 'AR_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\*', 'AR_MULTIPLICATION', 'AR_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\/', 'AR_DIVISION', 'AR_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('\%', 'AR_MODULO', 'AR_KEYWORD'));
        $oLexer->addRule(LexerRule::patternTypeName('=', 'ASSIGN', 'KEYWORD'));
        return $oLexer;
    }
}