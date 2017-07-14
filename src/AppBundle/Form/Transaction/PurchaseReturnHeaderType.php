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
use AppBundle\Entity\Transaction\PurchaseReturnDetail;

class PurchaseReturnHeaderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('transactionDate', DateType::class)
            ->add('shippingFee')
            ->add('note')
            ->add('isTax')
            ->add('purchaseReturnDetails', CollectionType::class, array(
                'entry_type' => PurchaseReturnDetailType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype_data' => new PurchaseReturnDetail(),
                'label' => false,
            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $purchaseReturnHeader = $event->getData();
                $options['service']->initialize($purchaseReturnHeader, $options['init']);
                $form = $event->getForm();
                $options = array('class' => 'AppBundle\Entity\Transaction\PurchaseInvoiceHeader');
                if (!empty($purchaseReturnHeader->getId())) {
                    $options['disabled'] = true;
                }
                $form->add('purchaseInvoiceHeader', EntityTextType::class, $options);
            })
            ->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use ($options) {
                $purchaseReturnHeader = $event->getData();
                $options['service']->finalize($purchaseReturnHeader);
            })
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Transaction\PurchaseReturnHeader'
        ));
        $resolver->setRequired(array('service', 'init'));
    }
}
