<?php

namespace LibBundle\Grid\SortOperator;

use Doctrine\Common\Collections\Criteria;

class DescendingType implements OperatorTypeInterface
{
    public static function getLabel()
    {
        return 'descending';
    }
    
    public static function getOrder()
    {
        return self::DESC;
    }
    
    public static function sort(Criteria $criteria, $name)
    {
        $orderings = $criteria->getOrderings();
        $orderings[$name] = 'desc';
        $criteria->orderBy($orderings);
    }
}
