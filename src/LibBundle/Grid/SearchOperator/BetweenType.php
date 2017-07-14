<?php

namespace LibBundle\Grid\SearchOperator;

use Doctrine\Common\Collections\Criteria;

class BetweenType implements OperatorTypeInterface
{
    public static function getLabel()
    {
        return 'between';
    }
    
    public static function getNumberOfInput()
    {
        return 2;
    }
    
    public static function search(Criteria $criteria, $name, array $values)
    {
        $expr = Criteria::expr();
        $criteria->andWhere($expr->andX($expr->gte($name, $values[0]), $expr->lte($name, $values[1])));
    }
}
