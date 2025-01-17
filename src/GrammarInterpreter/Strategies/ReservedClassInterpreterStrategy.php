<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;
use GrammiCore\GrammarParser\Tree\ClassDeclarationNode;
use GrammiCore\GrammarParser\Tree\IdentifierNode;

class ReservedClassInterpreterStrategy extends InterpreterStrategy {

    /**
     * 
     * Intepretate AssignmentExpression ASTNode
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        return $oNode;
        // $oClass = new ClassDeclarationNode(
        //     $oNode->getProperty('name'),
        //     [],
        //     []
        // );
        // $x = 23123;
        // $oArgument = $oNode->getProperty('argument');
        // return $this->getInterpreter()->run($oArgument);
    }

    private function wrapRuntimeObjectAsClass($oReservedClass) {
        return new ClassDeclarationNode(
            new IdentifierNode($oReservedClass->getProperty('name')),
            [],
            []
        );
    }
    
}
