<?php
namespace ForgeScript\Runner;

abstract class ForgeScriptReservedStruct {

    /**
     * 
     * @return string
     */
    abstract public function getType();
    
    /**
     * 
     * @return string
     */
    abstract public function getName();

    /**
     * 
     * @return mixed
     */
    abstract public function getRuntimeStruct();

    /**
     * Summary of config
     * @return array
     */
    public function config() {
        return [
            'type' => $this->getType(),
            'identifierName' => $this->getName(),
            'runtimeStruct' => $this->getRuntimeStruct()
        ];
    }

}