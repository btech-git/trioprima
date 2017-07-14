<?php

namespace LibBundle\Grid;

class WidgetsBuilder
{
    private $dataGridView;
    private $sortWidgetValue;
    private $searchWidgetValue;
    private $pageWidgetValue;
    
    public function __construct(DataGridView $dataGridView, SearchWidgetValue $searchWidgetValue, SortWidgetValue $sortWidgetValue, PageWidgetValue $pageWidgetValue)
    {
        $this->dataGridView = $dataGridView;
        $this->searchWidgetValue = $searchWidgetValue;
        $this->sortWidgetValue = $sortWidgetValue;
        $this->pageWidgetValue = $pageWidgetValue;
    }
    
    public function searchWidget($label = null)
    {
        $this->dataGridView->searchWidget = array();
        $this->dataGridView->searchWidget['label'] = ($label === null) ? 'search' : $label;
        
        return $this->searchWidgetValue;
    }
    
    public function sortWidget($label = null)
    {
        $this->dataGridView->sortWidget = array();
        $this->dataGridView->sortWidget['label'] = ($label === null) ? 'sort' : $label;
        
        return $this->sortWidgetValue;
    }
    
    public function pageWidget($label = null)
    {
        $this->dataGridView->pageWidget = array();
        $this->dataGridView->pageWidget['label'] = ($label === null) ? 'page' : $label;
        
        return $this->pageWidgetValue;
    }
}
