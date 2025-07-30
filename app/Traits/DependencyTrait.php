<?php
namespace App\Traits;

use ReflectionClass;

trait DependencyTrait {
    
    static protected function orderDependencies(array $objectList) {
        $className = get_called_class();
        $dependencyList = $className::listDependencies();
        $return = [];
        for ($i = 0; $i < count($dependencyList); $i++) {
            $return[] = null;
        }
        foreach ($objectList as $object) {
            foreach($dependencyList as $index => $dependency){
                if(is_a($object,$dependency)){
                    $return[$index] = $object;
                }
            }
        }
        return $return;
    }
        
    /**
     * Lists dependencies found at called class' constructor
     * @return array
     */
    static public function listDependencies(): array {
        $dependencies = [];
        $reflection = new ReflectionClass(get_called_class());
        foreach ($reflection->getConstructor()->getParameters() as $parameter) {
            $dependencies[] = $parameter->getType()->__toString();
        }
        return $dependencies;
    }
    
    static public function getInstance(array $dependencies = []) {
        $className = get_called_class();
        return new $className(...$className::orderDependencies($dependencies));
    }
}