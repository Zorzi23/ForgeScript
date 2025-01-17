<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarParser\Tree\ReturnStatementNode;
use GrammiCore\GrammarParser\Tree\ReturnStatementValueNode;

class BlockStatementInterpreterStrategy extends ProgramStatementInterpreterStrategy {

    /**
     * 
     * Intepretate Block Statement ASTNode
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $xResult = null;
        foreach ($this->iterateBody($oNode) as $oStatement) {
            $xResult = $this->getInterpreter()->run($oStatement);
            if($xResult instanceof ReturnStatementValueNode) {
                return $xResult->getProperty('value');
            }
            if($oStatement instanceof ReturnStatementNode) { 
                return new ReturnStatementValueNode($xResult);
            }
        }
        return $xResult;
    }
    
}
