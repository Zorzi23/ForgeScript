<?php
namespace ForgeScript\GrammarParser\Strategies;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use GrammiCore\GrammarParser\Tree\BlockStatementNode;

class BlockStatementParserStrategy extends ParserStrategy {

    /**
     * Parse a block statement
     * 
     * @return BlockStatementNode
     */
    public function parse() {
        $this->getParser()->expect('BLOCK_START');
        $aBody = [];
        while ($this->getParser()->peek()->getType() !== 'BLOCK_END') {
            $aBody[] = $this->getParser()->parseStatement();
        }
        $this->getParser()->expect('BLOCK_END');
        return new BlockStatementNode($aBody);
    }
}
