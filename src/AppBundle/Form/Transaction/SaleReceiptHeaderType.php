<?php

namespace AppBundle\Form\Transaction;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use LibBundle\Form\Type\EntityTextType;
use AppBundle\Entity\Transaction\SaleReceiptDetail;

class SaleReceiptHeaderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('transactionDate', DateType::class)
            ->add('note')
            ->add('saleReceiptDetails', CollectionType::class, array(
                'entry_type' => SaleReceiptDetailType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype_data' => new SaleReceiptDetail(),
                'label' => false,
            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($options) {
                $saleReceiptHeader = $event->getData();
                $options['service']->initialize($saleReceiptHeader, $options['init']);
                $form = $event->getForm();
                $options = array('class' => 'AppBundle\Entity\Master\Customer');
                if (!empty($saleReceiptHeader->getId())) {
                    $options['disabled'] = true;
                }
                $form->add('customer', EntityTextType::class, $options);
            })
            ->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use ($options) {
                $saleReceiptHeader = $event->getData();
                $options['service']->finalize($saleReceiptHeader);
            })
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Transaction\SaleReceiptHeader'
        ));
        $resolver->setRequired(array('service', 'init'));
    }
}
