<?php
namespace ForgeScript\GrammarInterpreter\Strategies;
use GrammiCore\GrammarInterpreter\Strategies\InterpreterStrategy;
use GrammiCore\GrammarParser\Tree\AbstractSyntaxTreeNode;
use GrammiCore\GrammarParser\Tree\AssignmentExpressionNode;
use GrammiCore\GrammarParser\Tree\CallExpressionNode;
use GrammiCore\GrammarParser\Tree\ClassDeclarationNode;
use GrammiCore\GrammarParser\Tree\FunctionDeclarationNode;
use GrammiCore\GrammarParser\Tree\IdentifierNode;
use GrammiCore\GrammarParser\Tree\InstantiableObjectNode;
use GrammiCore\GrammarParser\Tree\MemberExpressionNode;
use GrammiCore\GrammarParser\Tree\MethodDeclarationNode;
use GrammiCore\GrammarParser\Tree\NewExpressionNode;
use GrammiCore\GrammarParser\Tree\PropertyDeclarationNode;
use GrammiCore\GrammarParser\Tree\ReservedClassNode;
use ReflectionClass;

class MemberExpressionInterpreterStrategy extends InterpreterStrategy {

    /**
     * Interpret a Array ASTNode
     * 
     * @param mixed $oNode
     * @return mixed
     */
    public function interpret($oNode) {
        return $this->evaluateMemberExpression($oNode);
    }
    
    /**
     * Evaluate a member expression
     * 
     * @param mixed $oNode
     * @param mixed $xObjectValues
     * @return mixed
     */
    public function evaluateMemberExpression($oNode, $xObjectValues = null) {
        $oObject = $oNode->getProperty('object');
        $oProperty = $oNode->getProperty('property');
        $sObjectName = $oObject->getProperty('name');
        $xObjectValues = is_null($xObjectValues) ? $this->getInterpreter()->run($oObject) : $xObjectValues;
        if($this->getInterpreter()->getIO()->hasError()) {
            return;
        }
        $bInstantiableObjectNode = $this->isObjectMember($xObjectValues)
            || $this->isClassMember($xObjectValues);
        if($oProperty instanceof MemberExpressionNode) {
            $oPropertyObject = $oProperty->getProperty('object');
            if($bInstantiableObjectNode) {
                $xPropertyObjectValue = $this->accessObjectMember($xObjectValues, $oPropertyObject);
                return $this->evaluateMemberExpression(new MemberExpressionNode(
                    $oPropertyObject,
                    $oProperty->getProperty('property')
                ), $xPropertyObjectValue);
            }
            $xObjectKeyValue = $this->getInterpreter()->run($oPropertyObject);
            $aObjectValues = $this->accessArrayMember($xObjectValues, $xObjectKeyValue);
            return $this->evaluateMemberExpression(
                $oProperty,
                $aObjectValues ?: []
            );
        }
        $xPropertyKeyValue = $bInstantiableObjectNode 
            ? $oProperty
            : $this->getInterpreter()->run($oProperty);
        return $this->accessMember($xObjectValues, $xPropertyKeyValue);
    }

    /**
     * Throw an exception for invalid member access
     */
    public function memberAcessException() {
        $this->getInterpreter()->throwError('Cannot Acess member of non Literal or Array type');
    }

    /**
     * Access a member
     * 
     * @param mixed $xLeftMember
     * @param mixed $xRightMember
     * @return mixed
     */
    public function accessMember($xLeftMember, $xRightMember) {
        if(is_string($xLeftMember)) {
            return $this->accessStringMember($xLeftMember, $xRightMember);
        }
        if($this->isClassMember($xLeftMember)) {
            return $this->accessClassMember($xLeftMember, $xRightMember);
        }
        if($this->isObjectMember($xLeftMember)) {
            return $this->accessObjectMember($xLeftMember, $xRightMember);
        }
        if($this->isObjectMember($xLeftMember)) {
            return $this->accessObjectMember($xLeftMember, $xRightMember);
        }
        return $this->accessArrayMember($xLeftMember, $xRightMember);
    }
    
    /**
     * Check if a member is a class
     * 
     * @param mixed $oMember
     * @return bool
     */
    private function isClassMember($oMember) {
        return $oMember instanceof ClassDeclarationNode
            || $oMember instanceof ReservedClassNode;
    }

    /**
     * Check if a member is an object
     * 
     * @param mixed $oMember
     * @return bool
     */
    private function isObjectMember($oMember) {
        return $oMember instanceof InstantiableObjectNode;
    }

    /**
     * Access a string member
     * 
     * @param string $sLeftMember
     * @param mixed $xRightMember
     * @return string
     */
    private function accessStringMember($sLeftMember, $xRightMember) {
        if(!is_int($xRightMember)) { 
            $this->getInterpreter()->throwError('String access member just can be acessed by int values.');
        }
        return substr($sLeftMember, $xRightMember, 1);
    }

    /**
     * Access a class member
     * 
     * @param mixed $oClass
     * @param mixed $xRightMember
     * @return mixed
     */
    private function accessClassMember($oClass, $xRightMember) {
        if($oClass instanceof ReservedClassNode) {
            return $this->accessMemberReservedClass($oClass, $xRightMember);
        }
        $oTempClassObject = new NewExpressionNode($oClass->getProperty('name'), []);
        $oTempClassObject = $this->getInterpreter()->run($oTempClassObject);
        $sObjectId = $oTempClassObject->getProperty('id');
        $aStructs = array_merge($oClass->getProperty('properties'), $oClass->getProperty('methods'));
        $aStaticClassStructs = array_filter($aStructs, function($oStruct) {
            return $oStruct->getProperty('static') === true;
        });
        $aTempStructs = array_map(function($oStruct) {
            $oStruct->setProperty('static', 0);
            return $oStruct;
        }, $aStaticClassStructs);
        $aTempProperties = array_filter($aTempStructs, function($oStruct) {
            return $oStruct instanceof PropertyDeclarationNode;
        });
        $aTempMethods = array_filter($aTempStructs, function($oStruct) {
            return $oStruct instanceof MethodDeclarationNode;
        });
        $oTempClassObject->setProperty('properties', $aTempProperties);
        $oTempClassObject->setProperty('methods', $aTempMethods);
        $oTempClassDeclaration = new ClassDeclarationNode($oClass->getProperty('name'), $aTempProperties, $aTempMethods, $oClass->getProperty('parent'));
        $oTempClassObject->setProperty('classDeclaration', $oTempClassDeclaration);
        $oTempMemberAccess = new MemberExpressionNode(
            $oTempClassObject,
            $xRightMember
        );
        $xReturn = $this->evaluateMemberExpression($oTempMemberAccess, $oTempClassObject);
        $oTempClassObjectDeclaration = $oTempClassObject->getProperty('classDeclaration');
        $aStructs = array_merge($oTempClassObjectDeclaration->getProperty('properties'), $oTempClassObjectDeclaration->getProperty('methods'));
        array_walk($aStructs, function(&$oStruct) {
            $oStruct->setProperty('static', 1);
        });
        $this->getInterpreter()->removeEnvironmentDataFromCurrentScope('OBJECT', $sObjectId);
        return $xReturn;
    }

    /**
     * Access an object member
     * 
     * @param mixed $oObject
     * @param mixed $xRightMember
     * @return mixed
     */
    private function accessObjectMember($oObject, $xRightMember) {
        if($xRightMember instanceof CallExpressionNode) {
            return $this->accessObjectMethodMember($oObject, $xRightMember);
        }
        return $this->accessObjectPropertyMember($oObject, $xRightMember);
    }

    /**
     * Access an object method member
     * 
     * @param mixed $oObject
     * @param CallExpressionNode $oMethodCallExpression
     * @return mixed
     */
    private function accessObjectMethodMember($oObject, $oMethodCallExpression) {
        $oClassDeclaration = $oObject->getProperty('classDeclaration');
        if($oClassDeclaration instanceof ReservedClassNode) {
            return $this->accessMemberReservedClass($oObject, $oMethodCallExpression);
        }
        $oCallee = $oMethodCallExpression->getProperty('callee');
        $sMethodName = $oCallee->getProperty('name');
        $aMethodName = explode('.', $sMethodName);
        $sObjectId = $oObject->getProperty('id');
        if(count($aMethodName) === 2) {
            list($sMethodName) = array_reverse($aMethodName);
        }
        $sClassObjectMethod = strtr('{objectId}.{method}', [
            '{objectId}'=> $sObjectId,
            '{method}'=> $sMethodName,
        ]);
        $oCallee->setProperty('name', $sClassObjectMethod);
        $oMethodCallExpression->setProperty('callee', $oCallee);
        $sClassName = $oClassDeclaration->getProperty('name')->getProperty('name');
        $aClassMethods = $this->extractClassObjectMethods($oClassDeclaration);
        $oMethodDeclaration = $oClassDeclaration->getMethodFromList($sMethodName, $aClassMethods);
        if(!$oMethodDeclaration) {
            $this->getInterpreter()->throwError("Method {$sMethodName} does not exists on {$sClassName}");
        }
        $oCurrentThis = $this->getInterpreter()->getCurrentThis();
        $bInsideObjectAccess = false;
        if($oCurrentThis) {
            $bInsideObjectAccess = $oCurrentThis->getProperty('id') == $oObject->getProperty('id');
        }
        if($oMethodDeclaration->getProperty('modifier') == 'private' && $oMethodDeclaration->isInherited()) {
            $this->getInterpreter()->throwError('Cannot call private parent method');
        }
        if($oMethodDeclaration->getProperty('modifier') == 'protected' && $oMethodDeclaration->isInherited() && !$bInsideObjectAccess) {
            $this->getInterpreter()->throwError('Cannot call non protected method outside object context');
        }
        if($oMethodDeclaration->getProperty('modifier') != 'public' && !$bInsideObjectAccess) {
            $this->getInterpreter()->throwError('Cannot call non public method');
        }
        $oClassObjectMethodFunction = new FunctionDeclarationNode(
            new IdentifierNode($sClassObjectMethod),
            $oMethodDeclaration->getProperty('params') ?: [],
            $oMethodDeclaration->getProperty('body')
        );
        $oCallClassObjectMethod = new CallExpressionNode($oCallee, $oMethodCallExpression->getProperty('arguments'));
        $oNewThis = $oObject;
        $oParent = $oMethodDeclaration->isInherited() ? $oClassDeclaration->getProperty('parent') : null;
        while($oParent && (!$oMethodDeclaration || $oMethodDeclaration->isInherited())) {
            $oParentClassDeclaration = $oParent->getProperty('classDeclaration');
            $oNextMethodDeclaration = $oParentClassDeclaration->getMethodFromList($sMethodName, $oParentClassDeclaration->getProperty('methods'));
            $oNextParent = $oParentClassDeclaration->getProperty('parent');
            if(!$oNextParent && $oNextMethodDeclaration) {
                $oNewThis = $oParent;
                break;
            }
            $oParent = $oNextParent;
            $oMethodDeclaration = $oNextMethodDeclaration;
        }
        $this->getInterpreter()->setCurrentThis($oNewThis);
        $this->getInterpreter()->setFunctionEnvironmentValueOnCurrentScope($sClassObjectMethod, $oClassObjectMethodFunction);
        $xMethodReturn = CallExpressionInterpreterStrategy::interpreter($this->getInterpreter())
            ->setRemoveFunctionAfterCall(true)
            ->interpret($oCallClassObjectMethod);
        $this->getInterpreter()->setCurrentThis(null);
        return $xMethodReturn;
    }

    /**
     * Access an object property member
     * 
     * @param mixed $oLeftMember
     * @param mixed $xRightMember
     * @return mixed
     */
    private function accessObjectPropertyMember($oLeftMember, $xRightMember) {
        $sRightMemberProperty = null;
        if($xRightMember instanceof AssignmentExpressionNode) {
            $oLeft = $xRightMember->getProperty('left'); 
            if($oLeft instanceof IdentifierNode) {
                $sRightMemberProperty = $oLeft->getProperty('name');
            }
            else if($oLeft instanceof MemberExpressionNode) {
                $xRightMemberProperty = $oLeft->getProperty('object');
                $oIntermediateMemberAccess = new MemberExpressionNode($oLeftMember, $xRightMemberProperty);
                $xRightMemberLeftValue = $this->interpret($oIntermediateMemberAccess);
                $oAssignmentExpression = new AssignmentExpressionInterpreterStrategy();
                $oAssignmentExpression->setInterpreter($this->getInterpreter());
                $xIntermediateMemberAccessValue = $oAssignmentExpression->evaluateMemberExpressionAssignment(
                    $oLeft,
                    $xRightMember->getProperty('right'),
                    $xRightMemberLeftValue
                );
                $xRightMember = new AssignmentExpressionNode(
                    $xRightMemberProperty,
                    $xIntermediateMemberAccessValue
                );
                $sRightMemberProperty = $xRightMemberProperty->getProperty('name');
            }
        }
        else {
            $sRightMemberProperty = $xRightMember->getProperty('name');
        }
        $aObjectProperties = $this->extractClassObjectProperties($oLeftMember->getProperty('classDeclaration'));
        foreach($aObjectProperties as &$oProperty) {
            $oId = $oProperty->getProperty('id');
            $oIdentifier = $oId instanceof AssignmentExpressionNode
                ? $oId->getProperty('left')
                : $oId;
            $sIdName = $oIdentifier->getProperty('name');
            if($sIdName != $sRightMemberProperty) {
                continue;
            }
            $bInsideObjectAccess = false;
            $oCurrentThis = $this->getInterpreter()->getCurrentThis();
            if($oCurrentThis) {
                $bInsideObjectAccess = $oCurrentThis->getProperty('id') == $oLeftMember->getProperty('id');
            }
            if($oProperty->getProperty('modifier') == 'private' && $oProperty->isInherited()) {
                $this->getInterpreter()->throwError('Cannot access private parent property');
            }
            if($oProperty->getProperty('modifier') == 'protected' && $oProperty->isInherited()) {
                $this->getInterpreter()->throwError('Cannot access non protected property outside object context');
            }
            if($oProperty->getProperty('modifier') != 'public' && !$bInsideObjectAccess) {
                $this->getInterpreter()->throwError('Cannot access non public property');
            }
            if($oProperty->getProperty('static') === true) {
                $this->getInterpreter()->throwError('Cannot access static property as object property');
            }
            $oPropertyId = $oProperty->getProperty('id');
            if($oPropertyId instanceof AssignmentExpressionNode) {
                $xInitValue = $this->getInterpreter()->run($oPropertyId->getProperty('right'));
                $oProperty->setProperty('id', $oPropertyId->getProperty('left'));
                $oProperty->setProperty('value', $xInitValue);
            }
            if($xRightMember instanceof AssignmentExpressionNode) {
                $xRightMemberValue = $xRightMember->getProperty('right');
                $xValue = $xRightMemberValue instanceof AbstractSyntaxTreeNode 
                    ? $this->getInterpreter()->run($xRightMemberValue)
                    : $xRightMemberValue;
                $oProperty->setProperty('value', $xValue);
            }
            return $oProperty->getProperty('value');
        }
        $this->getInterpreter()->throwError("Property {$sRightMemberProperty} not found");
    }

    /**
     * Extract class object properties
     * 
     * @param mixed $oClassObject
     * @return array
     */
    private function extractClassObjectProperties($oClassObject) {
        return $this->extractClassObjectStruct($oClassObject, 'properties');
    }
    /**
     * Extract class object methods
     * 
     * @param mixed $oClassObject
     * @return array
     */
    private function extractClassObjectMethods($oClassObject) {
        return $this->extractClassObjectStruct($oClassObject, 'methods');
    }

    /**
     * Extract class object structure
     * 
     * @param mixed $oClassObject
     * @param string $sStruct
     * @return array
     */
    private function extractClassObjectStruct($oClassObject, $sStruct) {
        $aCurrentStruct = $oClassObject->getProperty($sStruct);
        $oParent = $oClassObject->getProperty('parent');
        if(!$oParent) {
            return array_map(function($oCurrentStruct) {
                $oCurrentStruct->setInherited(false);
                return $oCurrentStruct;
            }, $aCurrentStruct);
        }
        $oInstantiableParent = null;
        if($oParent instanceof IdentifierNode) {
            $aParentData = $this->getInterpreter()->getClassEnvironmentValue($oParent->getProperty('name'));
            $oParent = $aParentData['value'];
            $oInstantiableParent = NewExpressionInterpreterStrategy::interpreter($this->getInterpreter())
                ->interpret(new NewExpressionNode($oParent->getProperty('name'), []));
            $oClassObject->setProperty('parent', $oInstantiableParent);
            $oParent = $oInstantiableParent;
        }
        $oParentClassDeclaration = $oParent->getProperty('classDeclaration');
        $aParentStruct = $oParentClassDeclaration ? $this->extractClassObjectStruct($oParentClassDeclaration, $sStruct) : [];
        $aParentStruct = array_map(function($oParentProperty) use($oInstantiableParent) {
            $oParentProperty->setInherited(true);
            return $oParentProperty;
        }, $aParentStruct);
        return array_merge($aCurrentStruct, $aParentStruct);
    }

    /**
     * Access an array member
     * 
     * @param array $aLeftMember
     * @param mixed $xRightMember
     * @return mixed
     */
    private function accessArrayMember($aLeftMember, $xRightMember) {
        if(!is_array($aLeftMember)) { 
            $this->memberAcessException();
        }
        if(!isset($aLeftMember[$xRightMember])) { 
            return null;
        }
        return $aLeftMember[$xRightMember];
    }

    /**
     * Access a member of a reserved class
     * 
     * @param mixed $oObjectClass
     * @param mixed $oProperty
     * @return mixed
     */
    private function accessMemberReservedClass($oObjectClass, $oProperty) {
        $bStatic = $oObjectClass instanceof ReservedClassNode; 
        $oClass = $bStatic
            ? $oObjectClass
            : $oObjectClass->getProperty('classDeclaration');
        $sObjectName = $bStatic ? $oClass->getProperty('name') : $oObjectClass->getProperty('id');
        $xRuntimeObject = $oClass->getProperty('runtimeObject');
        $sRuntimeName = (new ReflectionClass($xRuntimeObject))->getName();
        $xRuntimeObject = $bStatic ? $sRuntimeName : $xRuntimeObject;
        if(!$bStatic && is_string($xRuntimeObject)) {
            $xRuntimeObject = new $xRuntimeObject();
        }
        if($oProperty instanceof CallExpressionNode) {
            $oCallee = $oProperty->getProperty('callee');
            $sMethodName = $oCallee->getProperty('name');
            $sObjectFunction = strtr('{objectName}.{method}', [
                '{objectName}'=> $sObjectName,
                '{method}'=> $sMethodName,
            ]);
            $oCallee->setProperty('name', $sObjectFunction);
            $this->getInterpreter()->addReservedFunction($sObjectFunction, [$xRuntimeObject, $sMethodName]);
            $xPropertyKeyValue = $this->getInterpreter()->run($oProperty);
            $oClass->setProperty('runtimeObject', $xRuntimeObject);
            $this->getInterpreter()->removeReservedFunction($sObjectFunction);
            return $xPropertyKeyValue;
        }
    }

}
