<?php

namespace LibBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class FormTypeExtension extends AbstractTypeExtension
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['label_attr'] = array_merge($view->vars['label_attr'], array(
            'class' => 'text-capitalize',
        ));
        $view->vars['attr'] = array_merge($view->vars['attr'], array(
            'novalidate' => 'novalidate',
        ));
    }

    public function getExtendedType()
    {
        return FormType::class;
    }
}
