<?php

namespace LibBundle\Grid;

abstract class WidgetValue
{
    protected $group = null;
    protected $field = null;
    protected $operator = null;
    
    public function addGroup($name, $label = null)
    {
        $this->group = $name;
        $this->field = null;
        $this->operator = null;
        
        if ($this->group === null) {
            return null;
        }
        
        return $this;
    }
    
    public function addField($name, $label = null)
    {
        $this->field = $name;
        $this->operator = null;
        
        if ($this->group === null || $this->field === null) {
            return null;
        }
        
        return $this;
    }
    
    public function setReferences(array $references)
    {
        if ($this->group === null || $this->field === null) {
            return null;
        }
        
        return $this;
    }
    
    public function setDefault($operator)
    {
        if (!$this->checkOperatorType($operator)) {
            return null;
        }
        
        if ($this->group === null || $this->field === null) {
            return null;
        }
        
        return $this;
    }
    
    public function addOperator($name, $label = null)
    {
        if (!$this->checkOperatorType($name)) {
            return null;
        }
        
        $this->operator = $name;
        
        if ($this->group === null || $this->field === null || $this->operator === null) {
            return null;
        }
        
        return $this;
    }
    
    private function checkOperatorType($name)
    {
        if (class_exists($name)) {
            $interfaces = class_implements($name);
            
            if (isset($interfaces[$this->getOperatorTypeInterface()])) {
                return true;
            }
        }
        
        return false;
    }
    
    protected abstract function getOperatorTypeInterface();
}
