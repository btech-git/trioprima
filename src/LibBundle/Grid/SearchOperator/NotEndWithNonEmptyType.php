<?php

namespace LibBundle\Grid\SearchOperator;

use Doctrine\Common\Collections\Criteria;

class NotEndWithNonEmptyType implements OperatorTypeInterface
{
    public static function getLabel()
    {
        return 'not end with';
    }
    
    public static function getNumberOfInput()
    {
        return 1;
    }
    
    public static function search(Criteria $criteria, $name, array $values)
    {
        if ($values[0] !== null && $values[0] !== '') {
            $criteria->andWhere(new \Doctrine\Common\Collections\Expr\Comparison($name, \LibBundle\Doctrine\Comparison::N_ENDS_WITH, $values[0]));
        }
    }
}
