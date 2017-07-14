<?php

namespace LibBundle\Grid;

class DataBuilder
{
    public $dataGridView;
    public $dataGridReference;
    
    public function __construct(DataGridView $dataGridView, DataGridReference $dataGridReference)
    {
        $this->dataGridView = $dataGridView;
        $this->dataGridReference = $dataGridReference;
    }
    
    public function processSearch(\Closure $call)
    {
        foreach ($this->dataGridView->searchVals as $group => $items) {
            foreach ($items as $field => $list) {
                $operator = $list[0];
                $values = array_slice($list, 1);
                
                $searchFieldReferences = $this->dataGridReference->getSearchFieldReferences($group, $field);
                if ($searchFieldReferences) {
                    $num = $operator::getNumberOfInput();
                    foreach ($searchFieldReferences as $i => $reference) {
                        $vals = array_slice($values, $i * $num, $num);
                        if (($dataTransformers = $this->dataGridReference->getDataTransformers($group, $field))) {
                            if (isset($dataTransformers[$i])) {
                                array_walk($vals, array($this->dataGridReference, 'toModel'), $dataTransformers[$i]);
                            }
                        }
                        $call($vals, $operator, $reference, $group);
                    }
                }
            }
        }
    }
    
    public function processSort(\Closure $call)
    {
        foreach ($this->dataGridView->sortVals as $group => $items) {
            foreach ($items as $field => $operator) {
                $sortFieldReferences = $this->dataGridReference->getSortFieldReferences($group, $field);
                if ($sortFieldReferences) {
                    foreach ($sortFieldReferences as $reference) {
                        $call($operator, $reference, $group);
                    }
                }
            }
        }
    }
    
    public function processPage($count, \Closure $call)
    {
        $this->setCount($count);
        
        $sizePage = $this->dataGridView->getSizePage();
        $currentPage = $this->dataGridView->getCurrentPage();
        $lastPage = $this->dataGridView->getLastPage();
        $offsetPage = $this->dataGridView->getOffsetPage();
        
        $call($offsetPage, $sizePage, $currentPage, $lastPage);
    }
    
    public function processExtra(\Closure $call)
    {
        $extra = $this->dataGridView->extra;
        $call($extra);
    }
    
    public function setCount($count)
    {
        $this->dataGridView->count = intval($count);
    }
    
    public function setData($data)
    {
        $this->dataGridView->data = $data;
    }
}
