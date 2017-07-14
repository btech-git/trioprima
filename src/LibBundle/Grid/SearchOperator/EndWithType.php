<?php

namespace LibBundle\Grid\SearchOperator;

use Doctrine\Common\Collections\Criteria;

class EndWithType implements OperatorTypeInterface
{
    public static function getLabel()
    {
        return 'end with';
    }
    
    public static function getNumberOfInput()
    {
        return 1;
    }
    
    public static function search(Criteria $criteria, $name, array $values)
    {
        $criteria->andWhere(new \Doctrine\Common\Collections\Expr\Comparison($name, \LibBundle\Doctrine\Comparison::ENDS_WITH, $values[0]));
    }
}
