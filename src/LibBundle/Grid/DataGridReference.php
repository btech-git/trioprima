<?php

namespace LibBundle\Grid;

use LibBundle\Grid\Transformer\DataTransformerInterface;

class DataGridReference
{
    private $searchReferences = array();
    private $sortReferences = array();
    
    public function setDataTransformers($group, $field, array $dataTransformers)
    {
        $this->searchReferences[$group][$field]['transformers'] = $dataTransformers;
        
        return $this->searchReferences[$group][$field]['transformers'];
    }
    
    public function getDataTransformers($group, $field)
    {
        if (isset($this->searchReferences[$group][$field]['transformers'])) {
            return $this->searchReferences[$group][$field]['transformers'];
        }
        else {
            return false;
        }
    }
    
    public function setSearchDefaultValues($group, $field, array $values)
    {
        $this->searchReferences[$group][$field]['defaults'] = $values;
        
        return $this->searchReferences[$group][$field]['defaults'];
    }
    
    public function getSearchDefaultValues($group, $field)
    {
        if (isset($this->searchReferences[$group][$field]['defaults'])) {
            return $this->searchReferences[$group][$field]['defaults'];
        }
        else {
            return false;
        }
    }
    
    public function setSearchFieldReferences($group, $field, array $references)
    {
        $this->searchReferences[$group][$field]['fields'] = $references;
        
        return $this->searchReferences[$group][$field]['fields'];
    }
    
    public function getSearchFieldReferences($group, $field)
    {
        if (isset($this->searchReferences[$group][$field]['fields'])) {
            return $this->searchReferences[$group][$field]['fields'];
        }
        else {
            return false;
        }
    }
    
    public function setSortDefaultValues($group, $field, array $values)
    {
        $this->sortReferences[$group][$field]['defaults'] = $values;
        
        return $this->sortReferences[$group][$field]['defaults'];
    }
    
    public function getSortDefaultValues($group, $field)
    {
        if (isset($this->sortReferences[$group][$field]['defaults'])) {
            return $this->sortReferences[$group][$field]['defaults'];
        }
        else {
            return false;
        }
    }
    
    public function setSortFieldReferences($group, $field, array $references)
    {
        $this->sortReferences[$group][$field]['fields'] = $references;
        
        return $this->sortReferences[$group][$field]['fields'];
    }
    
    public function getSortFieldReferences($group, $field)
    {
        if (isset($this->sortReferences[$group][$field]['fields'])) {
            return $this->sortReferences[$group][$field]['fields'];
        }
        else {
            return false;
        }
    }
    
    public function toView(&$item, $key, DataTransformerInterface $dataTransformer)
    {
        $item = $dataTransformer->toView($item);
    }
    
    public function toModel(&$item, $key, DataTransformerInterface $dataTransformer)
    {
        $item = $dataTransformer->toModel($item);
    }
}
