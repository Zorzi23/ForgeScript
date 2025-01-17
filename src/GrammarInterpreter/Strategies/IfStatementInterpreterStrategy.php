<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;

class IfStatementInterpreterStrategy extends InterpreterStrategy {

    /**
     * 
     * Intepretate ConditionStatement ASTNode
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $oTest = $this->getInterpreter()->run($oNode->getProperty('test'));
        if ($oTest) {
            return $this->getInterpreter()->run($oNode->getProperty('consequent'));
        }
        if ($xAlternate = $oNode->getProperty('alternate')) {
            return $this->getInterpreter()->run($xAlternate);
        }
    }
    
}
