<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;
use GrammiCore\GrammarParser\Tree\CallArgumentNode;
use GrammiCore\GrammarParser\Tree\ReturnStatementValueNode;

class CallExpressionInterpreterStrategy extends InterpreterStrategy {

    private $bRemoveFunctionAfterCall = false;

    public function isRemoveFunctionAfterCall()    {
        return $this->bRemoveFunctionAfterCall === true;
    }

    public function setRemoveFunctionAfterCall($bRemoveFunctionAfterCall) {
        $this->bRemoveFunctionAfterCall = $bRemoveFunctionAfterCall;
        return $this;
    }

    /**
     * 
     * Intepretate CallExpression ASTNode
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $oCallee = $oNode->getProperty('callee');
        $aArguments = $oNode->getProperty('arguments');
        $sFunctionName = $oCallee->getProperty('name');
        if($this->getInterpreter()->isReservedFunction($sFunctionName)) {
            return $this->evaluateReservedFunction($sFunctionName, $aArguments);
        }
        return $this->evaluateFunction($sFunctionName, $aArguments);
    }

    private function evaluateReservedFunction($sFunctionName, $aArguments) {
        $aArgumentsData = $this->extractArgumentsData($aArguments);
        $aReservedFunction = $this->getInterpreter()->getReservedFunction($sFunctionName);
        $xFunction = $aReservedFunction['function'];
        if(is_array($xFunction)) {
            list($sObject, $sMethod) = explode('.', $sFunctionName);
            list($xRuntimeObject) = $xFunction;
            $bStatic = is_string($xRuntimeObject);
            $oReflectionClass = new \ReflectionClass($xRuntimeObject);
            try {
                $oMethod = $oReflectionClass->getMethod($sMethod);
                if($bStatic !== $oMethod->isStatic()) {
                    $this->getInterpreter()->throwError(strtr('Method {methodName} exists on {objectName} but it is {static}', [
                        '{static}' => $bStatic ? 'static' : 'non static',
                        '{objectName}' => $sObject,
                        '{methodName}' => $sMethod
                    ]));
                }
            } 
            catch (\ReflectionException $oReflection) {
                $this->getInterpreter()->throwError(strtr('Method {methodName} does not exists on {objectName}', [
                    '{objectName}' => $sObject,
                    '{methodName}' => $sMethod
                ]));
            }
        }
        if($this->getInterpreter()->getIO()->hasError()) {
            return;
        }
        return call_user_func_array($xFunction, [
            $aArgumentsData,
            $this->getInterpreter()
        ]);
    }
    
    private function evaluateFunction($sFunctionName, $aArguments) {
        $aData = $this->getInterpreter()->getEnvironmentFunctionFromCurrentScope($sFunctionName, false);
        if(empty($aData)) {
            $aData = $this->getInterpreter()->getEnvironmentFunction(
                0,
                $sFunctionName
            );
        }
        if($this->getInterpreter()->getIO()->hasError()) { return; } 
        $oBody = $aData['value']['body'];
        $aParams = $aData['value']['params'];
        $aArgumentsValues = $this->extractArgumentsValues($this->extractArgumentsData($aArguments));
        $this->getInterpreter()->pushEnvironment();
        foreach($aParams as $iParam => $oParam) {
            $sParamName = $oParam->getProperty('name');
            if(!isset($aArgumentsValues[$iParam])) {
                $this->getInterpreter()->throwError("Missing param {$sParamName} on {$sFunctionName}");
                return;
            }
            $oArgumentValue = $aArgumentsValues[$iParam];
            $this->getInterpreter()->setVariableEnvironmentValueOnCurrentScope($sParamName, $oArgumentValue);
        }
        $xReturn = $this->getInterpreter()->run($oBody);
        $this->getInterpreter()->popEnvironment();
        if($this->isRemoveFunctionAfterCall()) {
            $this->getInterpreter()->removeEnvironmentDataFromCurrentScope('FUNCTION', $sFunctionName);
        }
        if($xReturn instanceof ReturnStatementValueNode) {
            $xReturn = $xReturn->getProperty('value');
        }
        return $xReturn;
    }

    public function extractArgumentsData($aArguments) {
        $aData = [];
        foreach($aArguments as $oArgument) {
            $xValue = $this->getInterpreter()->run($oArgument);
            $aData[] = new CallArgumentNode($oArgument, $xValue);
        }
        return $aData;
    }

    public function extractArgumentsValues($aArguments) {
        return array_map(function($oArg) {
            return $oArg->getProperty('value');
        }, $aArguments);
    }
    
}
