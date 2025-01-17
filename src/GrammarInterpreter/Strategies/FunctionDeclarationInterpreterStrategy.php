<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;
use GrammiCore\GrammarParser\Tree\IdentifierNode;
use GrammiCore\GrammarParser\Tree\LiteralNode;

class FunctionDeclarationInterpreterStrategy extends InterpreterStrategy {

    /**
     * 
     * Intepretate CallExpression ASTNode
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $oId = $oNode->getProperty('name');
        $sFunctionName = $oId->getProperty('name');
        if($this->getInterpreter()->isReservedFunction($sFunctionName)) {
            $this->getInterpreter()->throwError("Cannot declare {$sFunctionName} because it a reserved function");
        }
        $this->getInterpreter()->setFunctionEnvironmentValueOnCurrentScope($sFunctionName, $oNode);
    }
    
}
