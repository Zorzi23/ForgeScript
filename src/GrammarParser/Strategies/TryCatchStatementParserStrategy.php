<?php
namespace ForgeScript\GrammarParser\Strategies;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use GrammiCore\GrammarParser\Tree\IdentifierNode;
use GrammiCore\GrammarParser\Tree\TryCatchStatementNode;

class TryCatchStatementParserStrategy  extends ParserStrategy {

    /**
     * Parse a try/catch/finally statement
     * 
     * @return TryCatchStatementNode
     */
    public function parse() {
        $this->getParser()->expect('TRY_KEYWORD');
        $oBlock = BlockStatementParserStrategy::parser($this->getParser())->parse();
        $this->getParser()->expect('CATCH_KEYWORD');
        $this->getParser()->expect('PRECEDENCE_BLOCK_START');
        $oParam = $this->getParser()->expect('IDENTIFIER');
        $oParam = new IdentifierNode($oParam->getValue(), 'CATCH_EXCEPTION');
        $this->getParser()->expect('PRECEDENCE_BLOCK_END');
        $oHandler = BlockStatementParserStrategy::parser($this->getParser())->parse();
        $oFinally = null;
        $oPeek = $this->getParser()->peek();
        if ($oPeek && $oPeek->isType('FINALLY_KEYWORD')) {
            $this->getParser()->next();
            $oFinally = BlockStatementParserStrategy::parser($this->getParser())->parse();
        }
        return new TryCatchStatementNode($oBlock, $oHandler, $oParam, $oFinally);
    }

}
