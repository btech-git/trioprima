<?php

namespace LibBundle\Grid\SearchOperator;

use Doctrine\Common\Collections\Criteria;

class GreaterType implements OperatorTypeInterface
{
    public static function getLabel()
    {
        return 'greater';
    }
    
    public static function getNumberOfInput()
    {
        return 1;
    }
    
    public static function search(Criteria $criteria, $name, array $values)
    {
        $expr = Criteria::expr();
        $criteria->andWhere($expr->gt($name, $values[0]));
    }
}
