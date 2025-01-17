<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;

class SuperExpressionInterpreterStrategy extends InterpreterStrategy {

    public function interpret($oNode) {
        $oCurrentThis = $this->getInterpreter()->getCurrentThis();
        if(!$oCurrentThis) {
            return $this->getInterpreter()->throwError('Context Object not found on super access.');
        }
        $oSuper = $oCurrentThis->getProperty('classDeclaration')->getProperty('parent');
        if(!$oSuper) {
            return $this->getInterpreter()->throwError('Object has no parent.');
        }
        return $oSuper;
    }

}
