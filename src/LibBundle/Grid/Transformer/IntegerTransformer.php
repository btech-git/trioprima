<?php

namespace LibBundle\Grid\Transformer;

class IntegerTransformer implements DataTransformerInterface
{
    public function toView($value)
    {
        return $value === null ? '' : strval($value);
    }
    
    public function toModel($string)
    {
        return $string === '' ? null : intval($string);
    }
}
