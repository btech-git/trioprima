<?php

namespace LibBundle\Grid\SearchOperator;

use Doctrine\Common\Collections\Criteria;

class GreaterEqualType implements OperatorTypeInterface
{
    public static function getLabel()
    {
        return 'greater or equal';
    }
    
    public static function getNumberOfInput()
    {
        return 1;
    }
    
    public static function search(Criteria $criteria, $name, array $values)
    {
        $expr = Criteria::expr();
        $criteria->andWhere($expr->gte($name, $values[0]));
    }
}
