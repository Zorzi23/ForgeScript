<?php
namespace ForgeScript\Runner\ReservedStructs;
use ForgeScript\GrammarInterpreter\Strategies\AssignmentExpressionInterpreterStrategy;
use ForgeScript\Runner\ForgeScriptReservedStruct;
use GrammiCore\GrammarParser\Tree\AssignmentExpressionNode;
use GrammiCore\GrammarParser\Tree\IdentifierNode;
use GrammiCore\GrammarParser\Tree\MemberExpressionNode;
use GrammiCore\GrammarParser\Tree\UnsetAssignmentNode;

class UnsetFunction extends ForgeScriptReservedStruct {

    public function getType() {
        return 'FUNCTION';
    }

    public function getName() {
        return 'unset';
    }

    public function getRuntimeStruct() {
        return function() {
            return $this->unset(...func_get_args());
        };
    }

    protected function unset($aArgs, $oInterpreter) {
        foreach($aArgs as $oCallArgument) {
            $oArgument = $oCallArgument->getProperty('argument');
            if($oArgument instanceof MemberExpressionNode) {
                $oAssigmentExpressionNode = new AssignmentExpressionNode(
                    $oArgument,
                    new UnsetAssignmentNode()
                );
                $oAssignmentExpression = new AssignmentExpressionInterpreterStrategy();
                $oAssignmentExpression->setInterpreter($oInterpreter)->interpret($oAssigmentExpressionNode);
            }
            if(!($oArgument instanceof IdentifierNode)) {
                continue;            
    
            }
            $sIdentifierName = $oArgument->getProperty('name');
            $oInterpreter->removeEnvironmentDataFromCurrentScopeAndAnyType($sIdentifierName);
        }
        return null;
    }

}