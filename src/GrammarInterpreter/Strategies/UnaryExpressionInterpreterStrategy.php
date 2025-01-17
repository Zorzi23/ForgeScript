<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;

class UnaryExpressionInterpreterStrategy extends InterpreterStrategy {

    /**
     * Interpret a BinaryExpression ASTNode
     * 
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $sOperator = $oNode->getProperty('operator');
        $oArgument = $oNode->getProperty('argument');
        $sArgumentName = $oArgument->getProperty('name');
        $xArgumentValue = $this->getInterpreter()->run($oArgument);
        $bChangeIdValue = $oNode->getProperty('changeIdValue');
        $aOperatorMethods = static::getOperatorMethodMappings();
        if (!isset($aOperatorMethods[$sOperator])) {
            return $this->getInterpreter()->throwError("Unsupported Unary operator: $sOperator");
        }
        $xArgumentValue = call_user_func([$this, $aOperatorMethods[$sOperator]], $xArgumentValue);
        if($bChangeIdValue) {
            $this->getInterpreter()->setVariableEnvironmentValueOnCurrentScope($sArgumentName, $xArgumentValue);
        }
        return $xArgumentValue;
    }

    /**
     * Interpret the addition unary operator (++)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return mixed
     */
    protected function interpretAddition($xArgumentValue) {
        $this->testUnaryOperatorForArithmeticEvaluations($xArgumentValue);
        return $xArgumentValue + 1;
    }
    
    /**
     * Interpret the subtraction unary operator (--)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return mixed
     */
    protected function interpretSubtraction($xArgumentValue) {
        $this->testUnaryOperatorForArithmeticEvaluations($xArgumentValue);
        return $xArgumentValue - 1;
    }

    /**
     * Interpret the logical not unary operator (!)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return mixed
     */
    protected function interpretLogicalNot($xArgumentValue) {
        return !$xArgumentValue;
    }

    /**
     * Interpret the logical true unary operator (!!)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return mixed
     */
    protected function interpretLogicalTrue($xArgumentValue) {
        return !!$xArgumentValue;
    }

    protected function testUnaryOperatorForArithmeticEvaluations($xArgumentValue) {
        if(is_numeric($xArgumentValue)) {
            return true;
        }
        $this->getInterpreter()->throwError('Cannot evaluate Unary Arithmetic operation because the argument side of operation is not numeric.');
    }

    /**
     * Get the mappings of operators to interpretation methods
     * 
     * @return string[]
     */
    private static function getOperatorMethodMappings(): array {
        return [
            'UR_ADDITION'    => 'interpretAddition',
            'UR_SUBTRACTION' => 'interpretSubtraction',
            'UR_LOGICAL_NOT' => 'interpretLogicalNot',
            'UR_LOGICAL_TRUE' => 'interpretLogicalTrue',
        ];
    }

}