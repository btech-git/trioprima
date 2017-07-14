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
use AppBundle\Entity\Transaction\SaleInvoiceHeader;
use AppBundle\Entity\Transaction\SaleInvoiceDetail;

class SaleInvoiceHeaderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('transactionDate', DateType::class)
            ->add('customerInvoice')
            ->add('downpaymentType', ChoiceType::class, array(
                'choices' => ConstantValueList::get(SaleInvoiceHeader::class, 'DOWNPAYMENT_TYPE'),
                'choices_as_values' => true,
            ))
            ->add('downpaymentValue')
            ->add('discountType', ChoiceType::class, array(
                'choices' => ConstantValueList::get(SaleInvoiceHeader::class, 'DISCOUNT_TYPE'),
                'choices_as_values' => true,
            ))
            ->add('discountValue')
            ->add('shippingFee')
            ->add('note')
            ->add('isTax')
            ->add('customer', EntityTextType::class, array('class' => 'AppBundle\Entity\Master\Customer'))
            ->add('saleInvoiceDetails', CollectionType::class, array(
                'entry_type' => SaleInvoiceDetailType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype_data' => new SaleInvoiceDetail(),
                'label' => false,
            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($options) {
                $saleInvoiceHeader = $event->getData();
                $options['service']->initialize($saleInvoiceHeader, $options['init']);
                $form = $event->getForm();
                $options = array();
                if (!empty($saleInvoiceHeader->getId())) {
                    $options['disabled'] = true;
                }
                $form->add('codeNumberOrdinal', null, $options);
            })
            ->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use ($options) {
                $saleInvoiceHeader = $event->getData();
                $options['service']->finalize($saleInvoiceHeader);
            })
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Transaction\SaleInvoiceHeader'
        ));
        $resolver->setRequired(array('service', 'init'));
    }
}
