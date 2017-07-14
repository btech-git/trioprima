<?php

namespace LibBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

class IdentityTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        return $value;
    }
}
