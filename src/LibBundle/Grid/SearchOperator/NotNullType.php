<?php

namespace LibBundle\Grid\SearchOperator;

use Doctrine\Common\Collections\Criteria;

class NotNullType implements OperatorTypeInterface
{
    public static function getLabel()
    {
        return 'not null';
    }
    
    public static function getNumberOfInput()
    {
        return 0;
    }
    
    public static function search(Criteria $criteria, $name, array $values)
    {
        $expr = Criteria::expr();
        $criteria->andWhere($expr->neq($name, null));
    }
}
