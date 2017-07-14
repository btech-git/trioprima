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
use AppBundle\Entity\Transaction\PurchasePaymentDetail;

class PurchasePaymentHeaderType extends AbstractType
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
            ->add('paymentType')
            ->add('purchasePaymentDetails', CollectionType::class, array(
                'entry_type' => PurchasePaymentDetailType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype_data' => new PurchasePaymentDetail(),
                'label' => false,
            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($options) {
                $purchasePaymentHeader = $event->getData();
                $options['service']->initialize($purchasePaymentHeader, $options['init']);
                $form = $event->getForm();
                $options = array('class' => 'AppBundle\Entity\Master\Supplier');
                if (!empty($purchasePaymentHeader->getId())) {
                    $options['disabled'] = true;
                }
                $form->add('supplier', EntityTextType::class, $options);
            })
            ->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use ($options) {
                $purchasePaymentHeader = $event->getData();
                $options['service']->finalize($purchasePaymentHeader);
            })
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Transaction\PurchasePaymentHeader'
        ));
        $resolver->setRequired(array('service', 'init'));
    }
}
