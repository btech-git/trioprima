<?php

namespace LibBundle\Grid\Transformer;

class BooleanTransformer implements DataTransformerInterface
{
    public function toView($value)
    {
        return $value === null ? '' : ($value ? '1' : '0');
    }
    
    public function toModel($string)
    {
        return $string === '' ? null : boolval($string);
    }
}
