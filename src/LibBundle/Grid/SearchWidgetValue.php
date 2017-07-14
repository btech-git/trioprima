<?php

namespace LibBundle\Grid;

use LibBundle\Grid\SearchOperator\OperatorTypeInterface;

class SearchWidgetValue extends WidgetValue
{
    protected $dataGridView;
    protected $dataGridReference;
    protected $inputIndex = null;
    
    public function __construct(DataGridView $dataGridView, DataGridReference $dataGridReference)
    {
        $this->dataGridView = $dataGridView;
        $this->dataGridReference = $dataGridReference;
    }
    
    public function addGroup($name, $label = null)
    {
        if (!parent::addGroup($name, $label)) {
            return $this;
        }
        
        $this->dataGridView->searchWidget['groups'][$this->group] = array();
        $this->dataGridView->searchWidget['groups'][$this->group]['label'] = ($label === null) ? $this->group : $label;
        $this->dataGridView->searchWidget['groups'][$this->group]['entity'] = null;
        $this->dataGridView->searchWidget['groups'][$this->group]['fields'] = array();

        $this->dataGridView->searchVals[$this->group] = array();
        
        return $this;
    }
    
    public function setEntityName($class)
    {
        if ($this->group === null) {
            return $this;
        }
        
        $this->dataGridView->searchWidget['groups'][$this->group]['entity'] = $class;
        
        return $this;
    }
    
    public function addField($name, $label = null)
    {
        if (!parent::addField($name, $label)) {
            return $this;
        }
        
        $this->dataGridReference->setSearchFieldReferences($this->group, $this->field, array($name));
        $this->dataGridReference->setDataTransformers($this->group, $this->field, array(null));
        
        $this->dataGridView->searchWidget['groups'][$this->group]['fields'][$this->field] = array();
        $this->dataGridView->searchWidget['groups'][$this->group]['fields'][$this->field]['label'] = ($label === null) ? $this->field : $label;
        $this->dataGridView->searchWidget['groups'][$this->group]['fields'][$this->field]['operators'] = array();

        $this->dataGridView->searchVals[$this->group][$this->field] = array(null);
        
        return $this;
    }
    
    public function setReferences(array $references)
    {
        if (!parent::setReferences($references)) {
            return $this;
        }
        
        $this->dataGridReference->setSearchFieldReferences($this->group, $this->field, $references);
        $this->dataGridReference->setDataTransformers($this->group, $this->field, array_fill(0, count($references), null));
        
        return $this;
    }
    
    public function setDefault($operator)
    {
        if (!parent::setDefault($operator)) {
            return $this;
        }
        
        $args = func_get_args();
        $values = array();
        array_walk_recursive($args, function($value) use (&$values) { $values[] = $value; });
        
        if (($count = $this->getInputCount($this->group, $this->field, $operator)) !== count($values) - 1) {
            return $this;
        }
        
        $this->dataGridReference->setSearchDefaultValues($this->group, $this->field, $values);
        
        return $this;
    }
    
    public function setDataTransformer()
    {
        if ($this->group === null || $this->field === null) {
            return $this;
        }
        
        $dataTransformers = func_get_args();
        foreach ($dataTransformers as $dataTransformer) {
            if ($dataTransformer !== null && !($dataTransformer instanceof Transformer\DataTransformerInterface)) {
                return $this;
            }
        } 
        
        $this->dataGridReference->setDataTransformers($this->group, $this->field, $dataTransformers);
        
        return $this;
    }
    
    public function addOperator($name, $label = null)
    {
        if (!parent::addOperator($name, $label)) {
            return $this;
        }
        
        $count = $this->getInputCount($this->group, $this->field, $this->operator);
        $fieldItems = &$this->dataGridView->searchWidget['groups'][$this->group]['fields'][$this->field];
        if (empty($fieldItems['operators'])) {
            $this->dataGridView->searchVals[$this->group][$this->field][0] = $this->operator;
            for ($i = 1; $i <= $count; $i++) {
                $this->dataGridView->searchVals[$this->group][$this->field][$i] = null;
            }
        }
        $fieldItems['operators'][$this->operator] = array();
        $operatorItems = &$fieldItems['operators'][$this->operator];
        $operatorItems['label'] = ($label === null) ? $name::getLabel() : $label;
        $operatorItems['list'] = array();
        for ($i = 0; $i < $count; $i++) {
            $operatorItems['list'][$i]['choices'] = null;
            $operatorItems['list'][$i]['value'] = null;
            $operatorItems['list'][$i]['placeholder'] = null;
            $operatorItems['list'][$i]['startdelimiter'] = null;
            $operatorItems['list'][$i]['enddelimiter'] = null;
            $operatorItems['list'][$i]['attributes'] = null;
        }
        $this->inputIndex = null;
        
        return $this;
    }
    
    public function getInput($offset, $start = null)
    {
        if ($this->group === null || $this->field === null || $this->operator === null) {
            return $this;
        }
        
        $operator = $this->operator;
        $count = $this->getInputCount($this->group, $this->field, $this->operator);
        $num = ($start === null ? 0 : $start - 1) * $operator::getNumberOfInput() + $offset;
        
        $this->inputIndex = ($num < 1 || $num > $count) ? null : $num - 1;
        
        return $this;
    }
    
    public function setListData(array $inputData = null, \Closure $labelModifier = null, $initialValue = null)
    {
        $choices = $this->makeInputList($inputData, $labelModifier);
        $value = (empty($choices) || in_array($initialValue, $choices, true)) ? $initialValue : reset($choices);
        
        $this->setCurrentInputOptions('choices', $choices);
        $this->setCurrentInputOptions('value', $value);
        
        return $this;
    }
    
    public function setPlaceHolder($placeholder)
    {
        return $this->setCurrentInputOptions('placeholder', $placeholder);
    }
    
    public function setStartDelimiter($delimiter)
    {
        return $this->setCurrentInputOptions('startdelimiter', $delimiter);
    }
    
    public function setEndDelimiter($delimiter)
    {
        return $this->setCurrentInputOptions('enddelimiter', $delimiter);
    }
    
    public function setAttributes(array $attributes)
    {
        return $this->setCurrentInputOptions('attributes', empty($attributes) ? null : $attributes);
    }
    
    private function setCurrentInputOptions($name, $value)
    {
        if ($this->group === null || $this->field === null || $this->operator === null || $this->inputIndex === null) {
            return $this;
        }
        
        $this->dataGridView->searchWidget['groups'][$this->group]['fields'][$this->field]['operators'][$this->operator]['list'][$this->inputIndex][$name] = $value;
        
        return $this;
    }
    
    private function getInputCount($group, $field, $operator)
    {
        $searchFieldReferences = $this->dataGridReference->getSearchFieldReferences($group, $field);
        $count = $operator::getNumberOfInput() * ($searchFieldReferences ? count($searchFieldReferences) : 1);
        
        return $count;
    }
    
    private function makeInputList($inputData, $labelModifier)
    {
        if (!empty($inputData) && $labelModifier !== null) {
            $list = array();
            $i = 0;
            foreach ($inputData as $key => $item) {
                $label = $labelModifier($item, $key, $i++);
                $list[$label] = $item;
            }
        } else {
            $list = $inputData;
        }
        
        return $list;
    }
    
    protected function getOperatorTypeInterface()
    {
        return OperatorTypeInterface::class;
    }
}
