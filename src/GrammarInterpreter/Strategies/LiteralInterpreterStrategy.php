<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;

class LiteralInterpreterStrategy extends InterpreterStrategy {

    /**
     * Interpret a Literal ASTNode
     * 
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $sValue = $oNode->getProperty('value');
        $sRaw = $oNode->getProperty('raw');
        $aValueMappings = static::getLiteralTypeMappings();
        if (isset($aValueMappings[$sValue])) {
            return call_user_func([$this, $aValueMappings[$sValue]], $sRaw);
        }
        return $this->getInterpreter()->throwError("Unknown literal type: $sValue");
    }

    /**
     * Interpret the INTEGER literal type
     * 
     * @param mixed $sRaw
     * @return int
     */
    private function interpretateInteger($sRaw): int {
        return (int) $sRaw;
    }

    /**
     * Interpret the FLOAT literal type
     * 
     * @param mixed $sRaw
     * @return float
     */
    private function interpretateFloat($sRaw): float {
        return (float) $sRaw;
    }

    /**
     * Interpret the STRING literal type
     * 
     * @param mixed $sRaw
     * @return string
     */
    private function interpretateString($sRaw): string {
        $sRaw = trim($sRaw,"'\"");
        return (string) $sRaw;
    }

    /**
     * Get the mappings of literal types to interpretation methods
     * 
     * @return string[]
     */
    private static function getLiteralTypeMappings(): array {
        return [
            'INTEGER' => 'interpretateInteger',
            'FLOAT'   => 'interpretateFloat',
            'STRING'  => 'interpretateString'
        ];
    }
    
}
