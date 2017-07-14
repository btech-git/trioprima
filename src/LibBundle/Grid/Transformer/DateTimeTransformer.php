<?php

namespace LibBundle\Grid\Transformer;

class DateTimeTransformer implements DataTransformerInterface
{
    private $format;
    
    public function __construct($format)
    {
        $this->format = $format;
    }
    
    public function toView($object)
    {
        return $object === null ? '' : $object->format($this->format);
    }
    
    public function toModel($string)
    {
        return $string === '' ? null : new \DateTime($string);
    }
}
