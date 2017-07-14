<?php

namespace LibBundle\Grid\SearchOperator;

use Doctrine\Common\Collections\Criteria;

interface OperatorTypeInterface
{
    public static function getLabel();
    
    public static function getNumberOfInput();
    
    public static function search(Criteria $criteria, $name, array $values);
}
