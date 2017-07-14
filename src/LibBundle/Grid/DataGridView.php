<?php

namespace LibBundle\Grid;

class DataGridView
{
    public $count = 0;
    public $data = array();
    public $searchWidget = array();
    public $sortWidget = array();
    public $pageWidget = array();
    public $route = '';
    public $routeParams = array();
    public $routeQueries = array();
    
    public $id = '';
    public $defaultSearchVals = array();
    public $defaultSortVals = array();
    public $defaultPageVals = array();
    public $searchVals = array();
    public $sortVals = array();
    public $pageVals = array();
    public $extra = array();
    
    public function getDefaultParams()
    {
        return array(
            'id' => $this->id,
            'searchvals' => $this->defaultSearchVals,
            'sortvals' => $this->defaultSortVals,
            'pagevals' => $this->defaultPageVals,
            'extra' => array(),
        );
    }
    
    public function getParams()
    {
        return array(
            'id' => $this->id,
            'searchvals' => $this->searchVals,
            'sortvals' => $this->sortVals,
            'pagevals' => $this->pageVals,
            'extra' => $this->extra,
        );
    }
    
    public function isEmpty()
    {
        return empty($this->count);
    }
    
    public function getSizePage()
    {
        $pageSize = isset($this->pageVals['pagesize']) ? $this->pageVals['pagesize'] : false;
        
        if (!is_numeric($pageSize)) {
            $pageSize = $this->count;
        } else {
            $pageSize = ($pageSize < 1) ? 1 : $pageSize;
        }
        
        return intval($pageSize);
    }
    
    public function getCurrentPage()
    {
        $pageNum = isset($this->pageVals['pagenum']) ? $this->pageVals['pagenum'] : 0;
        
        $lastPage = $this->getLastPage();
        if (!is_numeric($pageNum)) {
            $pageNum = $lastPage;
        } else {
            if ($pageNum > $lastPage) {
                $pageNum = $lastPage;
            }
            $pageNum = ($pageNum < 1) ? 1 : $pageNum;
        }
        
        return intval($pageNum);
    }
    
    public function getLastPage()
    {
        $sizePage = $this->getSizePage();
        
        return ceil($this->count / $sizePage);
    }
    
    public function getOffsetPage()
    {
        return ($this->getCurrentPage() - 1) * $this->getSizePage();
    }
}
