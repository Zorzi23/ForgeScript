<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;
use GrammiCore\GrammarParser\Tree\CallExpressionNode;
use GrammiCore\GrammarParser\Tree\InstantiableObjectNode;
use GrammiCore\GrammarParser\Tree\MemberExpressionNode;
use GrammiCore\GrammarParser\Tree\UnsetAssignmentNode;

class AssignmentExpressionInterpreterStrategy extends InterpreterStrategy {

    /**
     * 
     * Intepretate AssignmentExpression ASTNode
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $oLeft = $oNode->getProperty('left');
        $oRight = $oNode->getProperty('right');
        $bMemberExpression = $oLeft instanceof MemberExpressionNode; 
        if(!$bMemberExpression) {
            $this->getInterpreter()->run($oLeft);
        }
        $oKeyProperty = $bMemberExpression
            ? $oLeft->getProperty('object') 
            : $oLeft;
        $xKey = $oKeyProperty->getProperty('name');
        $xValue = $bMemberExpression 
            ? $this->evaluateMemberExpressionAssignment($oLeft, $oRight)
            : $this->getInterpreter()->run($oRight);
        $this->getInterpreter()->setVariableEnvironmentValueOnCurrentScope(
            $xKey,
            $xValue
        );
    }
    
    public function evaluateMemberExpressionAssignment($oLeft, $oRight, &$xLeftObjectValue = null) {
        $oLeftObject = $oLeft->getProperty('object');
        $oLeftProperty = $oLeft->getProperty('property');
        $xRightValue = $oRight instanceof UnsetAssignmentNode ? $oRight : $this->getInterpreter()->run($oRight);
        $xLeftObjectValue = is_null($xLeftObjectValue) ? $this->getInterpreter()->run($oLeftObject) : $xLeftObjectValue;
        if(!($oLeftProperty instanceof MemberExpressionNode)) {
            $xLeftPropertyValue = $this->getInterpreter()->run($oLeftProperty);
            if($xRightValue instanceof UnsetAssignmentNode) {
                unset($xLeftObjectValue[$xLeftPropertyValue]);
            }
            else {
                $xLeftObjectValue[$xLeftPropertyValue] = $xRightValue;
            }
            return $xLeftObjectValue;
        }
        $oInterLeftObject = $oLeftProperty->getProperty('object');
        $bInstantiableObject = $xLeftObjectValue instanceof InstantiableObjectNode;
        $bInterCallExpression = $oInterLeftObject instanceof CallExpressionNode;
        $bInterObjectCallExpression = $bInstantiableObject && $bInterCallExpression;
        $xInterLeftObjectPropertyValue = null;
        if($bInterObjectCallExpression) {
            $oMember = new MemberExpressionNode($xLeftObjectValue, $oInterLeftObject);
            $xInterLeftObjectPropertyValue = $this->getInterpreter()->run($oMember);
        }
        else {
            $xInterLeftObjectPropertyValue = $this->getInterpreter()->run($oInterLeftObject); 
        }
        $oInterLeftProperty = $oLeftProperty->getProperty('property');
        $oMemberInterpreter = MemberExpressionInterpreterStrategy::interpreter($this->getInterpreter());
        $oInterMemberExpression = new MemberExpressionNode($oLeftObject, $oInterLeftObject);
        $xInterLeftObjectValue = $bInterObjectCallExpression ? $xInterLeftObjectPropertyValue : $oMemberInterpreter->evaluateMemberExpression($oInterMemberExpression, $xLeftObjectValue);
        $oNextMemberExpression = new MemberExpressionNode($oInterLeftObject, $oInterLeftProperty);
        $this->evaluateMemberExpressionAssignment($oNextMemberExpression, $oRight, $xInterLeftObjectValue);
        if($xLeftObjectValue instanceof InstantiableObjectNode) {
            return $xLeftObjectValue;
        }
        $xLeftObjectValue[$xInterLeftObjectPropertyValue] = $xInterLeftObjectValue;
        return $xLeftObjectValue;
    }

}
