<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;

class ObjectExpressionInterpreterStrategy extends InterpreterStrategy {

    /**
     * Interpret a Array ASTNode
     * 
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $aProperties = $oNode->getProperty('properties');
        $oKey = $aProperties['key'];
        $oValue = $aProperties['value'];
        return [
            'key' => $this->getInterpreter()->run($oKey),
            'value' => $this->getInterpreter()->run($oValue),
        ];
    }

}
