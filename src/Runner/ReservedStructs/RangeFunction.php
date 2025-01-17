<?php
namespace ForgeScript\Runner\ReservedStructs;
use ForgeScript\GrammarInterpreter\Strategies\CallExpressionInterpreterStrategy;
use ForgeScript\Runner\ForgeScriptReservedStruct;

class RangeFunction extends ForgeScriptReservedStruct {

    public function getType() {
        return 'FUNCTION';
    }

    public function getName() {
        return 'range';
    }

    public function getRuntimeStruct() {
        return function() {
            return $this->range(...func_get_args());
        };
    }

    protected function range($aArgs, $oInterpreter) {
        return range(...CallExpressionInterpreterStrategy::interpreter($oInterpreter)->extractArgumentsValues($aArgs));
    }

}