<?php
namespace ForgeScript\GrammarParser\Strategies;
use GrammiCore\GrammarParser\Strategies\ParserStrategy;
use GrammiCore\GrammarParser\Tree\ClassDeclarationNode;
use GrammiCore\GrammarParser\Tree\IdentifierNode;
use GrammiCore\GrammarParser\Tree\MethodDeclarationNode;
use GrammiCore\GrammarParser\Tree\PropertyDeclarationNode;

class ClassDeclarationParserStrategy extends ParserStrategy {

    /**
     * Parse a class declaration
     * 
     * @return ClassDeclarationNode
     */
    public function parse() {
        $this->getParser()->expect('CLASS_KEYWORD');
        $oId = $this->getParser()->expect('IDENTIFIER');
        $oIdentifier = new IdentifierNode($oId->getValue(), 'CLASS');
        $oParentClassIdentifier = null;
        if($this->getParser()->peek()->isType('EXTENDS_KEYWORD')) {
            $this->getParser()->next();
            $oParentClassIdentifierToken = $this->getParser()->expect('IDENTIFIER');
            $oParentClassIdentifier = new IdentifierNode($oParentClassIdentifierToken->getValue());
        }
        $this->getParser()->expect('BLOCK_START');
        $aproperties = [];
        $aMethods = [];
        while ($this->getParser()->peek()->getType() !== 'BLOCK_END') {
            $oStruct = $this->parseClassStructDeclaration();
            if($oStruct instanceof PropertyDeclarationNode) {
                $aproperties[] = $oStruct; 
                continue;
            }
            if($oStruct instanceof MethodDeclarationNode) {
                $aMethods[] = $oStruct; 
                continue;
            }
            $this->getParser()->next();
        }
        $this->getParser()->expect('BLOCK_END');
        return new ClassDeclarationNode(
            $oIdentifier,
            $aproperties,
            $aMethods,
            $oParentClassIdentifier
        );
    }

    private function parseClassStructDeclaration() {
        $aModifiersTypes = self::modifiersTypes();
        $sModifier = 'public';
        if($this->getParser()->peek()->isType(array_keys($aModifiersTypes))) {
            $sModifier = $aModifiersTypes[$this->getParser()->peek()->getType()];
            $this->getParser()->next();
        }
        $bStatic = false;
        if($this->getParser()->peek()->isType('STATIC_KEYWORD')) {
            $this->getParser()->next();
            $bStatic = true;
        }
        $bPropertyDeclaration = $this->getParser()->peek()->isType('PROPERTY_KEYWORD');
        if($bPropertyDeclaration) {
            return $this->parsePropertyDeclaration($sModifier, $bStatic);
        }
        $bMethodDeclaration = $this->getParser()->peek()->isType(['METHOD_KEYWORD', 'FUNCTION_KEYWORD']);
        if($bMethodDeclaration) {
            return $this->parseMethodDeclaration($sModifier, $bStatic);
        }
    }

    /**
     * Parse a method declaration
     * 
     * @return PropertyDeclarationNode|null
     */
    private function parsePropertyDeclaration($sModifier, $bStatic) {
        $this->getParser()->next();
        $oId = $this->getParser()->parseStatement();
        $oProp = new PropertyDeclarationNode($oId, $sModifier);
        $oProp->setProperty('static', $bStatic);
        return $oProp;
    }

    /**
     * Parse a method declaration
     * 
     * @return MethodDeclarationNode|null
     */
    private function parseMethodDeclaration($sModifier, $bStatic) {
        $this->getParser()->next();
        $oFunctionDeclaration = FunctionDeclarationParserStrategy::parser($this->getParser())->parseFunction();
        return new MethodDeclarationNode(
            $oFunctionDeclaration->getProperty('name'),
            $oFunctionDeclaration->getProperty('params'),
            $oFunctionDeclaration->getProperty('body'),
            $sModifier,
            $bStatic
        );
    }

    public static function modifiersTypes() {
        return [
            'PUBLIC_MODIFIER_KEYWORD'    => 'public',
            'PRIVATE_MODIFIER_KEYWORD'   => 'private',
            'PROTECTED_MODIFIER_KEYWORD' => 'protected',
        ];
    }

}
