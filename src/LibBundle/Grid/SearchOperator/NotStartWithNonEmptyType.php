<?php

namespace LibBundle\Grid\SearchOperator;

use Doctrine\Common\Collections\Criteria;

class NotStartWithNonEmptyType implements OperatorTypeInterface
{
    public static function getLabel()
    {
        return 'not start with';
    }
    
    public static function getNumberOfInput()
    {
        return 1;
    }
    
    public static function search(Criteria $criteria, $name, array $values)
    {
        if ($values[0] !== null && $values[0] !== '') {
            $criteria->andWhere(new \Doctrine\Common\Collections\Expr\Comparison($name, \LibBundle\Doctrine\Comparison::N_STARTS_WITH, $values[0]));
        }
    }
}
