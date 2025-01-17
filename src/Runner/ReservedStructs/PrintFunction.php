<?php
namespace ForgeScript\Runner\ReservedStructs;
use ForgeScript\Runner\ForgeScriptReservedStruct;

class PrintFunction extends ForgeScriptReservedStruct {

    public function getType() {
        return 'FUNCTION';
    }

    public function getName() {
        return 'print';
    }

    public function getRuntimeStruct() {
        return function() {
            return $this->print(...func_get_args());
        };
    }

    protected function print($aArgs, $oInterpreter) {
        if($oInterpreter->getIO()->hasError()) {
            return;
        }
        $aArgsValues = array_map(function($oArg) {
            return $oArg->getProperty('value');
        }, $aArgs);
        foreach($aArgsValues as $xValue) {
            $oInterpreter->getIO()->appendStdOut($xValue);
        }
    }

}