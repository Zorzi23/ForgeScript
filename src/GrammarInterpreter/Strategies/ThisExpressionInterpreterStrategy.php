<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;

class ThisExpressionInterpreterStrategy extends InterpreterStrategy {

    public function interpret($oNode) {
        return $this->getInterpreter()->getCurrentThis();
    }

}
