<?php

namespace LibBundle\Grid\SortOperator;

use Doctrine\Common\Collections\Criteria;

class BlankType implements OperatorTypeInterface
{
    public static function getLabel()
    {
        return '';
    }
    
    public static function getOrder()
    {
        return '';
    }
    
    public static function sort(Criteria $criteria, $name)
    {
    }
}
