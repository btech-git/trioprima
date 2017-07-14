<?php

namespace LibBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use LibBundle\Form\Transformer\IdentityTransformer;

class TextTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new IdentityTransformer());
    }

    public function getExtendedType()
    {
        return TextType::class;
    }
}
