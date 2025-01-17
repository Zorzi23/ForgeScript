<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;
use GrammiCore\GrammarParser\Tree\CallExpressionNode;

class BinaryExpressionInterpreterStrategy extends InterpreterStrategy {

    /**
     * Interpret a BinaryExpression ASTNode
     * 
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $xLeft = $this->getInterpreter()->run($oNode->getProperty('left'));
        $xRight = $this->getInterpreter()->run($oNode->getProperty('right'));
        $sOperator = $oNode->getProperty('operator');
        $aOperatorMethods = static::getOperatorMethodMappings();
        if (!isset($aOperatorMethods[$sOperator])) {
            return $this->getInterpreter()->throwError("Unsupported operator: $sOperator");
        }
        return call_user_func([$this, $aOperatorMethods[$sOperator]], $xLeft, $xRight);
    }

    /**
     * Interpret the equality operator (==)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return bool
     */
    protected function interpretEqual($xLeft, $xRight) {
        return $xLeft == $xRight;
    }

    /**
     * Interpret the equality operator (===)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return bool
     */
    protected function interpretEqualType($xLeft, $xRight) {
        return $xLeft === $xRight;
    }

    /**
     * Interpret the not equal operator (!=)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return bool
     */
    protected function interpretNotEqual($xLeft, $xRight) {
        return $xLeft != $xRight;
    }

    /**
     * Interpret the not equal operator (!=)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return bool
     */
    protected function interpretNotEqualType($xLeft, $xRight) {
        return $xLeft !== $xRight;
    }

    /**
     * Interpret the less than operator (<)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return bool
     */
    protected function interpretLessThan($xLeft, $xRight) {
        return $xLeft < $xRight;
    }

    /**
     * Interpret the less than or equal operator (<=)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return bool
     */
    protected function interpretLessThanEqual($xLeft, $xRight) {
        return $xLeft <= $xRight;
    }

    /**
     * Interpret the greater than operator (>)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return bool
     */
    protected function interpretGreaterThan($xLeft, $xRight) {
        return $xLeft > $xRight;
    }

    /**
     * Interpret the greater than or equal operator (>=)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return bool
     */
    protected function interpretGreaterThanEqual($xLeft, $xRight) {
        return $xLeft >= $xRight;
    }

    /**
     * Interpret the addition operator (+)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return mixed
     */
    protected function interpretAddition($xLeft, $xRight) {
        if(is_string($xLeft) && is_string($xRight)) {
            return $this->concat($xLeft, $xRight);
        }
        $this->testBinaryOperatorForArithmeticEvaluations($xLeft, $xRight);
        return $xLeft + $xRight;
    }
    
    /**
     * Interpret concat operation
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return mixed
     */
    protected function concat($xLeft, $xRight) {
        return $xLeft . $xRight;
    }

    /**
     * Interpret the subtraction operator (-)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return mixed
     */
    protected function interpretSubtraction($xLeft, $xRight) {
        $this->testBinaryOperatorForArithmeticEvaluations($xLeft, $xRight);
        return $xLeft - $xRight;
    }

    /**
     * Interpret the multiplication operator (*)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return mixed
     */
    protected function interpretMultiplication($xLeft, $xRight) {
        $this->testBinaryOperatorForArithmeticEvaluations($xLeft, $xRight);
        return $xLeft * $xRight;
    }

    /**
     * Interpret the division operator (/)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return mixed
     */
    protected function interpretDivision($xLeft, $xRight) {
        $this->testBinaryOperatorForArithmeticEvaluations($xLeft, $xRight);
        if ($xRight == 0) {
            return $this->getInterpreter()->throwError("Division by zero not allowed");
        }
        return $xLeft / $xRight;
    }

    /**
     * Interpret the modulo operator (%)
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return mixed
     */
    protected function interpretModulo($xLeft, $xRight) {
        $this->testBinaryOperatorForArithmeticEvaluations($xLeft, $xRight);
        return $xLeft % $xRight;
    }

    /**
     * Interpret the AND operation
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return mixed
     */
    protected function interpretLogicalAnd($xLeft, $xRight) {
        return $xLeft && $xRight;
    }

    /**
     * Interpret the OR operation
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return mixed
     */
    protected function interpretLogicalOr($xLeft, $xRight) {
        return $xLeft || $xRight;
    }

    /**
     * Interpret the XOR operation
     * 
     * @param mixed $xLeft
     * @param mixed $xRight
     * @return mixed
     */
    protected function interpretLogicalXor($xLeft, $xRight) {
        return $xLeft ^ $xRight;
    }

    protected function testBinaryOperatorForArithmeticEvaluations($xLeft, $xRight) {
        // if((is_numeric($xLeft) || $xLeft instanceof CallExpressionNode) && is_numeric($xRight)) {
        //     return true;
        // }
        return true;
        // $this->getInterpreter()->throwError('Cannot evaluate Arithmetic operation because the left and right side of operation is not numeric.');
    }

    /**
     * Get the mappings of operators to interpretation methods
     * 
     * @return string[]
     */
    private static function getOperatorMethodMappings(): array {
        return [
            'COND_EQUAL'            => 'interpretEqual',
            'COND_EQUAL_TYPE'       => 'interpretEqualType',
            'COND_NOT_EQUAL'        => 'interpretNotEqual',
            'COND_NOT_EQUAL_TYPE'   => 'interpretNotEqualType',
            'COND_LESS'             => 'interpretLessThan',
            'COND_LESS_OR_EQUAL'    => 'interpretLessThanEqual',
            'COND_GREATER'          => 'interpretGreaterThan',
            'COND_GREATER_OR_EQUAL' => 'interpretGreaterThanEqual',
            'AR_ADDITION'           => 'interpretAddition',
            'AR_SUBTRACTION'        => 'interpretSubtraction',
            'AR_MULTIPLICATION'     => 'interpretMultiplication',
            'AR_DIVISION'           => 'interpretDivision',
            'AR_MODULO'             => 'interpretModulo',
            'LOGICAL_AND'           => 'interpretLogicalAnd',
            'LOGICAL_OR'            => 'interpretLogicalOr',
            'LOGICAL_XOR'           => 'interpretLogicalXor',
        ];
    }

}