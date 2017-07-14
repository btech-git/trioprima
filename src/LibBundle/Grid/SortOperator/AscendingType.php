<?php

namespace LibBundle\Grid\SortOperator;

use Doctrine\Common\Collections\Criteria;

class AscendingType implements OperatorTypeInterface
{
    public static function getLabel()
    {
        return 'ascending';
    }
    
    public static function getOrder()
    {
        return self::ASC;
    }
    
    public static function sort(Criteria $criteria, $name)
    {
        $orderings = $criteria->getOrderings();
        $orderings[$name] = 'asc';
        $criteria->orderBy($orderings);
    }
}
