<?php

namespace LibBundle\Grid;

use Symfony\Component\HttpFoundation\Request;

class ParamsBuilder
{
    private $dataGridView;
    
    public function __construct(DataGridView $dataGridView)
    {
        $this->dataGridView = $dataGridView;
    }
    
    public function processRequest(Request $request)
    {
        $this->dataGridView->route = $request->attributes->get('_route');
        $this->dataGridView->routeParams = $request->attributes->get('_route_params');
        $this->dataGridView->routeQueries = $request->query->all();
        
        $this->dataGridView->defaultSortVals = $this->dataGridView->sortVals;
        $this->dataGridView->defaultSearchVals = $this->dataGridView->searchVals;
        $this->dataGridView->defaultPageVals = $this->dataGridView->pageVals;
        
        $paramBag = $request->isMethod(Request::METHOD_GET) ? $request->query : $request->request;
        
        $params['id'] = $paramBag->get('id', $this->dataGridView->id);
        $params['sortvals'] = $paramBag->get('sortvals', $this->dataGridView->sortVals);
        $params['searchvals'] = $paramBag->get('searchvals', $this->dataGridView->searchVals);
        $params['pagevals'] = $paramBag->get('pagevals', $this->dataGridView->pageVals);
        $params['extra'] = $paramBag->get('extra', $this->dataGridView->extra);
        
        $this->dataGridView->id = $params['id'];
        $this->dataGridView->sortVals = $params['sortvals'];
        $this->dataGridView->searchVals = $params['searchvals'];
        $this->dataGridView->pageVals = $params['pagevals'];
        $this->dataGridView->extra = $params['extra'];
        
        $sortWidget = $this->dataGridView->sortWidget;
        foreach ($this->dataGridView->sortVals as $group => $items) {
            $sortWidget['groups'][$group]['fields'] = array();
            foreach (array_keys($items) as $field) {
                $sortWidget['groups'][$group]['fields'][$field] = $this->dataGridView->sortWidget['groups'][$group]['fields'][$field];
            }
        }
        $this->dataGridView->sortWidget = $sortWidget;
    }
}
