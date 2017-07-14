<?php

namespace LibBundle\Grid;

use LibBundle\Grid\SortOperator\OperatorTypeInterface;

class SortWidgetValue extends WidgetValue
{
    protected $dataGridView;
    protected $dataGridReference;
    
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
        
        $this->dataGridView->sortWidget['groups'][$this->group] = array();
        $this->dataGridView->sortWidget['groups'][$this->group]['label'] = ($label === null) ? $this->group : $label;
        $this->dataGridView->sortWidget['groups'][$this->group]['fields'] = array();

        $this->dataGridView->sortVals[$this->group] = array();
        
        return $this;
    }
    
    public function addField($name, $label = null)
    {
        if (!parent::addField($name, $label)) {
            return $this;
        }
        
        
        $this->dataGridReference->setSortFieldReferences($this->group, $this->field, array($name));
        
        $this->dataGridView->sortWidget['groups'][$this->group]['fields'][$this->field] = array();
        $this->dataGridView->sortWidget['groups'][$this->group]['fields'][$this->field]['label'] = ($label === null) ? $this->field : $label;
        $this->dataGridView->sortWidget['groups'][$this->group]['fields'][$this->field]['operators'] = array();

        $this->dataGridView->sortVals[$this->group][$this->field] = '';
        
        return $this;
    }
    
    public function setReferences(array $references)
    {
        if (!parent::setReferences($references)) {
            return $this;
        }
        
        $this->dataGridReference->setSortFieldReferences($this->group, $this->field, $references);
        
        return $this;
    }
    
    public function setDefault($operator)
    {
        if (!parent::setDefault($operator)) {
            return $this;
        }
        
        $this->dataGridReference->setSortDefaultValues($this->group, $this->field, array($operator));
        
        return $this;
    }
    
    public function addOperator($name, $label = null)
    {
        if (!parent::addOperator($name, $label)) {
            return $this;
        }
        
        $fieldItems = &$this->dataGridView->sortWidget['groups'][$this->group]['fields'][$this->field];
        if (empty($fieldItems['operators'])) {
            $this->dataGridView->sortVals[$this->group][$this->field] = $this->operator;
        }
        $fieldItems['operators'][$this->operator] = array();
        $operatorItems = &$fieldItems['operators'][$this->operator];
        $operatorItems['label'] = ($label === null) ? $name::getLabel() : $label;
        $operatorItems['order'] = $name::getOrder();
        
        return $this;
    }
    
    protected function getOperatorTypeInterface()
    {
        return OperatorTypeInterface::class;
    }
}
