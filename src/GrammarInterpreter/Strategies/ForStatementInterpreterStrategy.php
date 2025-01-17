<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;
use GrammiCore\GrammarParser\Tree\AssignmentExpressionNode;

class ForStatementInterpreterStrategy extends InterpreterStrategy {

    /**
     * Interpret a Array ASTNode
     * 
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $oInit = $oNode->getProperty('init');
        $this->getInterpreter()->run($oInit);
        list($oInitVarDeclarator) = $oInit->getProperty('declarations');
        $oInitIdentifier = $oInitVarDeclarator->getProperty('id');
        $oTest = $oNode->getProperty('test');
        $oBody = $oNode->getProperty('body');
        $oUpdate = $oNode->getProperty('update');
        $bValidTest = $this->getInterpreter()->run($oTest);
        while($bValidTest) {
            $this->getInterpreter()->run($oBody);
            if($this->getInterpreter()->getIO()->hasError()) {
                break;
            }
            $oAssignmentExpression = new AssignmentExpressionNode($oInitIdentifier, $oUpdate);
            $this->getInterpreter()->run($oAssignmentExpression);
            $bValidTest = $this->getInterpreter()->run($oTest);
        }
    }
}
