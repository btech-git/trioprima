<?php

namespace LibBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

class ZeroToNullTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return $value === 0 ? null : $value;
    }

    public function reverseTransform($value)
    {
        return $value === null ? 0 : $value;
    }
}
