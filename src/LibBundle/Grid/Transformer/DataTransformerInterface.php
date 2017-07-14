<?php

namespace LibBundle\Grid\Transformer;

interface DataTransformerInterface
{
    public function toView($value);
    
    public function toModel($value);
}
