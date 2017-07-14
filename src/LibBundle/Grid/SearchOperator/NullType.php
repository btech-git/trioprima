<?php

namespace LibBundle\Grid\SearchOperator;

use Doctrine\Common\Collections\Criteria;

class NullType implements OperatorTypeInterface
{
    public static function getLabel()
    {
        return 'null';
    }
    
    public static function getNumberOfInput()
    {
        return 0;
    }
    
    public static function search(Criteria $criteria, $name, array $values)
    {
        $expr = Criteria::expr();
        $criteria->andWhere($expr->isNull($name));
    }
}
