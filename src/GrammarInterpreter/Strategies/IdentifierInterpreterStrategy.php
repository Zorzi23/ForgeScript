<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;
use GrammiCore\GrammarParser\Tree\ReservedClassNode;

class IdentifierInterpreterStrategy extends InterpreterStrategy {

    /**
     * 
     * Intepretate Identifier ASTNode
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $sName = $oNode->getProperty('name');
        if($this->getInterpreter()->isReservedClass($sName)) {
            $aReservedClass = $this->getInterpreter()->getReservedClass($sName);
            return new ReservedClassNode($sName, $aReservedClass['runtimeObject']);
        }
        $aData = $this->getInterpreter()->getEnvironmentDataFromAnyTypeCurrentScope($sName, false);
        if(!$aData) { return null; }
        return $this->getInterpreter()->extractValueOfEnvironmentData($aData);
    }
    
}
