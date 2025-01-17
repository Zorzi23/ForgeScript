<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;

class ClassDeclarationInterpreterStrategy extends InterpreterStrategy {

    /**
     * Interpret a ClassDeclaration ASTNode
     * 
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $sClassName = $oNode->getProperty('name')->getProperty('name'); 
        $bReserved = $this->getInterpreter()->isreservedClass($sClassName);
        if($bReserved) {
            return $this->getInterpreter()->throwError("Cannot redeclare {$sClassName} because they are reserved.");
        }
        $this->getInterpreter()->setClassEnvironmentValue(
            $sClassName,
            $oNode
        );
    }

}
