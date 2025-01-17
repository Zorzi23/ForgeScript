<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;
use GrammiCore\GrammarParser\Tree\CallExpressionNode;
use GrammiCore\GrammarParser\Tree\InstantiableObjectNode;
use GrammiCore\GrammarParser\Tree\MemberExpressionNode;
use GrammiCore\GrammarParser\Tree\ReservedClassNode;

class NewExpressionInterpreterStrategy extends InterpreterStrategy {

    /**
     * 
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $oClass = $oNode->getProperty('class');
        $sClassName = $oClass->getProperty('name');
        $oClassDeclaration = $this->getInterpreter()->run($oClass);
        if(!$oClassDeclaration) {
            return $this->getInterpreter()->throwError("Class {$sClassName} not found");
        }
        $oObject = $this->instantiateClass($sClassName, $oClassDeclaration);
        $this->getInterpreter()->setEnvironmentTypeValueOnCurrentScope('OBJECT', $oObject->getProperty('id'),$oObject);
        $oObject = $this->callConstructor($oObject, $oNode->getProperty('arguments'));
        return $oObject;
    }

    public function instantiateClass($sClassName, $oClassDeclaration) {
        return new InstantiableObjectNode(
            uniqid($sClassName),
            $oClassDeclaration
        );
    }

    private function callConstructor($oInstance, $aInitArguments) {
        $aInitArguments = CallExpressionInterpreterStrategy::interpreter($this->getInterpreter())
            ->extractArgumentsData($aInitArguments);
        $oClassDeclaration = $oInstance->getProperty('classDeclaration');
        if($oClassDeclaration instanceof ReservedClassNode) {
            $this->callReservedClassConstructor($oClassDeclaration, $aInitArguments);
            return $oInstance;
        }
        $oConstructorMethod = $oClassDeclaration->getMethodFromName('construct');
        if(!$oConstructorMethod) {
            return $oInstance;
        }
        $oConstructorCall = new CallExpressionNode($oInstance, $aInitArguments);
        $oConstructorMember = new MemberExpressionNode($oInstance, $oConstructorCall);
        $this->getInterpreter()->run($oConstructorMember);
        return $oInstance;
    }

    private function callReservedClassConstructor($oClassDeclaration, $aInitArguments) {
        $xRuntime = $oClassDeclaration->getProperty('runtimeObject');
        $xRuntime = new $xRuntime(...[$aInitArguments, $this->getInterpreter()]);
        $oClassDeclaration->setProperty('runtimeObject', $xRuntime);
        return $oClassDeclaration;
    }

}
