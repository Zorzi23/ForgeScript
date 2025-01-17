<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;
use GrammiCore\Engine\ErrorHandling\LanguageGrammarInterpreterError;

class TryCatchStatementInterpreterStrategy extends InterpreterStrategy {

    /**
     * Interpret a Array ASTNode
     * 
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $oTryBlock = $oNode->getProperty('try');
        $oCatchBlock = $oNode->getProperty('catch');
        $oCatchArgument = $oNode->getProperty('catchArgument');
        try {
            return $this->getInterpreter()->run($oTryBlock);
        } 
        catch (LanguageGrammarInterpreterError $oError) {
            $this->getInterpreter()->getIO()->clearStdErr();
            $sArgumentName = $oCatchArgument->getProperty('name');
            $oInterpreterThrowable = $oError->getInterpreterThrowable();
            $this->getInterpreter()->setVariableEnvironmentValueOnCurrentScope(
                $sArgumentName,
                $oInterpreterThrowable
            );
            $this->getInterpreter()->run($oCatchBlock);
            $this->getInterpreter()->removeEnvironmentDataFromCurrentScope(
                'VARIABLE',
                $sArgumentName
            );
        }
    }
}
