<?php
namespace ForgeScript\GrammarInterpreter\Strategies;

class InstantiableObjectInterpreterStrategy extends ProgramStatementInterpreterStrategy {

    /**
     * 
     * Intepretate Block Statement ASTNode
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        return $oNode;
    }
    
}
