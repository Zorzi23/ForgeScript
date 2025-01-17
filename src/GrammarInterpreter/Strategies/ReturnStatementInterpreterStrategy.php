<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;

class ReturnStatementInterpreterStrategy extends InterpreterStrategy {

    /**
     * 
     * Intepretate AssignmentExpression ASTNode
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $oArgument = $oNode->getProperty('argument');
        return $this->getInterpreter()->run($oArgument);
    }
    
}
