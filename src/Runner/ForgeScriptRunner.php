<?php
namespace ForgeScript\Runner;

use FilesystemIterator;
use ForgeScript\Lexer\ForgeScriptLexerBuilder;
use GrammiCore\GrammarInterpreter\LanguageGrammarInterpreter;
use GrammiCore\GrammarParser\LanguageGrammarParser;
use SplFileInfo;

class ForgeScriptRunner {

    public function run($sSource) {
        $xNextStepParam = $sSource;
        foreach($this->steps() as $sLanguageIntepretationStep) {
            $xNextStepParam = call_user_func([$this, $sLanguageIntepretationStep], $xNextStepParam);
        }
        return $xNextStepParam;
    }

    protected function tokenize($sSource) {
        $oLexerBuilder = ForgeScriptLexerBuilder::build();
        return $oLexerBuilder->tokenize($sSource);
    }

    protected function parseTokens($aTokens) {
        $oParser = new LanguageGrammarParser($aTokens);
        $oParser->setStrategyNamespaceName('ForgeScript\GrammarParser');
        return [$oParser->parse(), $oParser->getIO()];
    }

    protected function interpret($aParseResult = []) {
        list($oAst, $oIO) = $aParseResult;
        if($oIO->hasError()) {
            return $oIO;
        }
        $oInterpreter = $this->configInterpreter();
        $oInterpreter->setIO($oIO);
        $oInterpreter->run($oAst);
        return $oInterpreter->getIO();
    }

    protected function configInterpreter() {
        $oInterpreter = new LanguageGrammarInterpreter();
        $oInterpreter->setStrategyNamespaceName('ForgeScript\GrammarInterpreter');
        $this->loadReservedStructs($oInterpreter);
        return $oInterpreter;
    }

    protected function loadReservedStructs(LanguageGrammarInterpreter $oInterpreter) {
        $sReservedStructsDir = __DIR__ . '/ReservedStructs';
        $oFileIterator = new FilesystemIterator($sReservedStructsDir);
        foreach($oFileIterator as $oFile) {
            $aReservedStructConfig = $this->loadReservedStructFromFile($oFile);
            $sType = $aReservedStructConfig['type'];
            $sIdName = $aReservedStructConfig['identifierName'];
            $xRuntimeStruct = $aReservedStructConfig['runtimeStruct'];
            if($sType == 'FUNCTION') {
                $oInterpreter->addReservedFunction($sIdName, $xRuntimeStruct);
                continue;
            }
            if($sType == 'CLASS') {
                $oInterpreter->addReservedClass($sIdName, $xRuntimeStruct);
                continue;
            }
        }
    }

    protected function loadReservedStructFromFile(SplFileInfo $oFile) {
        $sStructName = strtr('{namespace}\\ReservedStructs\\{fileName}', [
            '{namespace}' => __NAMESPACE__,
            '{fileName}' => $oFile->getBasename('.php'),
        ]);
        $oStruct = new $sStructName();
        return $oStruct->config();
    }

    protected function steps() {
        return [
            'tokenize',
            'parseTokens',
            'interpret'
        ];
    }

}