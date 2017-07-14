<?php

namespace LibBundle\Grid\SortOperator;

use Doctrine\Common\Collections\Criteria;

interface OperatorTypeInterface
{
    const ASC = 'asc';
    const DESC = 'desc';
    
    public static function getLabel();
    
    public static function getOrder();
    
    public static function sort(Criteria $criteria, $name);
}
