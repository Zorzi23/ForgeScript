<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;

class ProgramStatementInterpreterStrategy extends InterpreterStrategy {

    /**
     * 
     * Intepretate Program or Block Statement ASTNode
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $xResult = null;
        foreach ($this->iterateBody($oNode) as $oStatement) {
            $xResult = $this->getInterpreter()->run($oStatement);
        }
        return $xResult;
    }

    /**
     * 
     * Iterate body of block statement
     * @param mixed $oNode
     * @return mixed
     */
    protected function iterateBody($oNode) {
        foreach ($oNode->getProperty('body') ?: [] as $oStatement) {
            yield $oStatement;
        }
    }
    
}
