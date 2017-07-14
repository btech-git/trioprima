<?php

namespace AppBundle\Form\Transaction;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use LibBundle\Form\Type\EntityTextType;
use LibBundle\Util\ConstantValueList;
use AppBundle\Entity\Transaction\PurchaseInvoiceHeader;
use AppBundle\Entity\Transaction\PurchaseInvoiceDetail;

class PurchaseInvoiceHeaderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('transactionDate', DateType::class)
            ->add('supplierInvoice')
            ->add('taxInvoiceCode')
            ->add('downpaymentType', ChoiceType::class, array(
                'choices' => ConstantValueList::get(PurchaseInvoiceHeader::class, 'DOWNPAYMENT_TYPE'),
                'choices_as_values' => true,
            ))
            ->add('downpaymentValue')
            ->add('discountType', ChoiceType::class, array(
                'choices' => ConstantValueList::get(PurchaseInvoiceHeader::class, 'DISCOUNT_TYPE'),
                'choices_as_values' => true,
            ))
            ->add('discountValue')
            ->add('shippingFee')
            ->add('note')
            ->add('isTax')
            ->add('supplier', EntityTextType::class, array('class' => 'AppBundle\Entity\Master\Supplier'))
            ->add('purchaseInvoiceDetails', CollectionType::class, array(
                'entry_type' => PurchaseInvoiceDetailType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype_data' => new PurchaseInvoiceDetail(),
                'label' => false,
            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($options) {
                $purchaseInvoiceHeader = $event->getData();
                $options['service']->initialize($purchaseInvoiceHeader, $options['init']);
            })
            ->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use ($options) {
                $purchaseInvoiceHeader = $event->getData();
                $options['service']->finalize($purchaseInvoiceHeader);
            })
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Transaction\PurchaseInvoiceHeader'
        ));
        $resolver->setRequired(array('service', 'init'));
    }
}
