<?php

namespace AppBundle\Form\Transaction;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use LibBundle\Form\Type\EntityHiddenType;

class SaleInvoiceDetailType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('productName', null, array(
                'attr' => array('style' => 'width: 300px')
            ))
            ->add('quantity', null, array(
                'attr' => array('style' => 'width: 100px')
            ))
            ->add('unitPrice', null, array(
                'attr' => array('style' => 'width: 150px')
            ))
            ->add('discount', null, array(
                'attr' => array('style' => 'width: 150px')
            ))
            ->add('product', EntityHiddenType::class, array('class' => 'AppBundle\Entity\Master\Product'))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Transaction\SaleInvoiceDetail'
        ));
    }
}
