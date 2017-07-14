<?php

namespace LibBundle\Grid\SearchOperator;

use Doctrine\Common\Collections\Criteria;

class NotEqualNonEmptyType implements OperatorTypeInterface
{
    public static function getLabel()
    {
        return 'not equal';
    }
    
    public static function getNumberOfInput()
    {
        return 1;
    }
    
    public static function search(Criteria $criteria, $name, array $values)
    {
        if ($values[0] !== null && $values[0] !== '') {
            $expr = Criteria::expr();
            $criteria->andWhere($expr->neq($name, $values[0]));
        }
    }
}
