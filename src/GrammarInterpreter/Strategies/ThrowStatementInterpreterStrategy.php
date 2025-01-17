<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;
use GrammiCore\Engine\ReservedClass\InterpreterThrowable;
use GrammiCore\GrammarParser\Tree\InstantiableObjectNode;
use GrammiCore\GrammarParser\Tree\ReservedClassNode;

class ThrowStatementInterpreterStrategy extends InterpreterStrategy {

    /**
     * Interpret a ThrowStatement ASTNode
     * 
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $oArgument = $oNode->getProperty('argument');
        $oArgument = $this->getInterpreter()->run($oArgument);
        $oThrowAbleObject = $this->getThrowAbleArgumentObject($oArgument);
        if($oThrowAbleObject instanceof InterpreterThrowable) {
            return $this->getInterpreter()->throwException($oArgument);
        }
        return $this->getInterpreter()->throwError('Cannot throw non throwable objects');
    }

    private function getThrowAbleArgumentObject($oArgument) {
        if(!($oArgument instanceof InstantiableObjectNode)) {
            return false;
        }
        $oClassDeclaration = $oArgument->getProperty('classDeclaration'); 
        if(!($oClassDeclaration instanceof ReservedClassNode)) {
            return false;
        }
        return $oClassDeclaration->getProperty('runtimeObject');
    }

}
