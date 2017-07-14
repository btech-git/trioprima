<?php

namespace LibBundle\Grid\SearchOperator;

use Doctrine\Common\Collections\Criteria;

class BlankType implements OperatorTypeInterface
{
    public static function getLabel()
    {
        return '';
    }
    
    public static function getNumberOfInput()
    {
        return 0;
    }
    
    public static function search(Criteria $criteria, $name, array $values)
    {
    }
}
