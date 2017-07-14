<?php

namespace LibBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use LibBundle\Form\Transformer\ZeroToNullTransformer;

class NumberTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ZeroToNullTransformer());
    }

    public function getExtendedType()
    {
        return NumberType::class;
    }
}
