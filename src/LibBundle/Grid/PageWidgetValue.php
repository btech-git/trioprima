<?php

namespace LibBundle\Grid;

class PageWidgetValue
{
    private $dataGridView;
    private $field = null;
    
    public function __construct(DataGridView $dataGridView)
    {
        $this->dataGridView = $dataGridView;
    }
    
    public function addPageSizeField($label = null)
    {
        $this->field = 'pagesize';
        
        if ($this->field === null) {
            return $this;
        }
        
        $this->dataGridView->pageWidget['fields'][$this->field] = array();
        $this->dataGridView->pageWidget['fields'][$this->field]['label'] = ($label === null) ? 'size (per page)' : $label;
        $this->dataGridView->pageWidget['fields'][$this->field]['choices'] = array();

        $this->dataGridView->pageVals[$this->field] = 10;
        
        return $this;
    }
    
    public function addPageNumField($label = null)
    {
        $this->field = 'pagenum';
        
        if ($this->field === null) {
            return $this;
        }
        
        $this->dataGridView->pageWidget['fields'][$this->field] = array();
        $this->dataGridView->pageWidget['fields'][$this->field]['label'] = ($label === null) ? 'go to (page #)' : $label;
        $this->dataGridView->pageWidget['fields'][$this->field]['choices'] = array();

        $this->dataGridView->pageVals[$this->field] = 1;
        
        return $this;
    }
    
    public function addItems()
    {
        if ($this->field === null) {
            return $this;
        }
        
        foreach (func_get_args() as $item) {
            if (empty($this->dataGridView->pageWidget['fields'][$this->field]['choices'])) {
                $this->dataGridView->pageVals[$this->field] = $item;
            }
            $this->dataGridView->pageWidget['fields'][$this->field]['choices'][] = $item;
        }
        
        return $this;
    }
    
    public function setDefault($default)
    {
        if ($this->field === null) {
            return $this;
        }
        
        $choices = $this->dataGridView->pageWidget['fields'][$this->field]['choices'];
        if (empty($choices) || in_array($default, $choices, true)) {
            $this->dataGridView->pageVals[$this->field] = $default;
        }
        
        return $this;
    }
}
