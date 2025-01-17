<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;

class VariableDeclarationInterpreterStrategy extends InterpreterStrategy {

    /**
     * 
     * Intepretate VariableDeclaration ASTNode
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        $kind = $oNode->getProperty('kind');
        if (!in_array($kind, ['var'], true)) {
            $this->getInterpreter()->throwError("Unsupported variable kind: $kind");
        }
        $aDeclarations = [];
        foreach ($oNode->getProperty('declarations') as $oDeclaration) {
            $sId = $oDeclaration->getProperty('id')->getProperty('name');
            $xInit = $this->getInterpreter()->run($oDeclaration->getProperty('init'));
            $aDeclarations[] = $xInit;
            $this->getInterpreter()->setVariableEnvironmentValueOnCurrentScope($sId, $xInit);
        }
        return $aDeclarations;
    }
    
}
