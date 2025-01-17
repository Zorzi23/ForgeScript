<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;

class ArrayExpressionInterpreterStrategy extends InterpreterStrategy {

    /**
     * Interpret a Array ASTNode
     * 
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $aEvaluatedElements = [];
        $aElements = $oNode->getProperty('elements');
        foreach($aElements as $oElement) {
            list($sKey, $oValue) = array_values($this->getInterpreter()->run($oElement));
            $aEvaluatedElements[$sKey] = $oValue;
        }
        return $aEvaluatedElements;
    }

}
