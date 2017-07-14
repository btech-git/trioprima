<?php

namespace AppBundle\Grid\Report;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectRepository;
use LibBundle\Grid\DataGridType;
use LibBundle\Grid\WidgetsBuilder;
use LibBundle\Grid\DataBuilder;
use LibBundle\Grid\SortOperator\BlankType as SortBlankType;
use LibBundle\Grid\SortOperator\AscendingType;
use LibBundle\Grid\SortOperator\DescendingType;
use LibBundle\Grid\SearchOperator\BlankType as SearchBlankType;
use LibBundle\Grid\SearchOperator\EqualType;
use LibBundle\Grid\SearchOperator\ContainType;
use LibBundle\Grid\SearchOperator\BetweenType;
use AppBundle\Entity\Transaction\SalePaymentHeader;
use AppBundle\Entity\Master\Customer;

class SalePaymentHeaderGridType extends DataGridType
{
    /**
     * @param WidgetsBuilder $builder
     * @param array $options
     */
    public function buildWidgets(WidgetsBuilder $builder, array $options)
    {
        $builder->searchWidget()
            ->addGroup('salePaymentHeader')
                ->setEntityName(SalePaymentHeader::class)
                ->addField('transactionDate')
                    ->addOperator(SearchBlankType::class)
                    ->addOperator(BetweenType::class)
                        ->getInput(1)
                            ->setAttributes(array('data-pick' => 'date'))
                        ->getInput(2)
                            ->setAttributes(array('data-pick' => 'date'))
            ->addGroup('customer')
                ->setEntityName(Customer::class)
                ->addField('company')
                    ->addOperator(SearchBlankType::class)
                    ->addOperator(EqualType::class)
                    ->addOperator(ContainType::class)
        ;

        $builder->sortWidget()
            ->addGroup('salePaymentHeader')
                ->addField('transactionDate')
                    ->addOperator(SortBlankType::class)
                    ->addOperator(AscendingType::class)
                    ->addOperator(DescendingType::class)
            ->addGroup('customer')
                ->addField('company')
                    ->addOperator(SortBlankType::class)
                    ->addOperator(AscendingType::class)
                    ->addOperator(DescendingType::class)
        ;

        $builder->pageWidget()
            ->addPageSizeField()
                ->addItems(10, 25, 50, 100)
            ->addPageNumField()
        ;
    }

    /**
     * @param DataBuilder $builder
     * @param ObjectRepository $repository
     * @param array $options
     */
    public function buildData(DataBuilder $builder, ObjectRepository $repository, array $options)
    {
        $criteria = Criteria::create();
        $criteria2 = Criteria::create();
        $associations = array(
            'customer' => array('criteria' => $criteria2, 'merge' => true),
        );

        $builder->processSearch(function($values, $operator, $field, $group) use ($criteria, $criteria2) {
            if ($group === 'customer') {
                $operator::search($criteria2, $field, $values);
            } else {
                $operator::search($criteria, $field, $values);
            }
        });

        $builder->processSort(function($operator, $field, $group) use ($criteria, $criteria2) {
            if ($group === 'customer') {
                $operator::sort($criteria2, $field);
            } else {
                $operator::sort($criteria, $field);
            }
        });

        $builder->processPage($repository->count($criteria, $associations), function($offset, $size) use ($criteria) {
            $criteria->setMaxResults($size);
            $criteria->setFirstResult($offset);
        });
        
        $objects = $repository->match($criteria, $associations);

        $builder->setData($objects);
    }
}
