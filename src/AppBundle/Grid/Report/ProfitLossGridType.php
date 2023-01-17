<?php

namespace AppBundle\Grid\Report;

use Doctrine\Common\Persistence\ObjectRepository;
use LibBundle\Grid\DataGridType;
use LibBundle\Grid\WidgetsBuilder;
use LibBundle\Grid\DataBuilder;
use LibBundle\Grid\SearchOperator\LessEqualType;
use AppBundle\Entity\Report\JournalLedger;

class ProfitLossGridType extends DataGridType
{
    public function buildWidgets(WidgetsBuilder $builder, array $options)
    {
        $builder->searchWidget()
            ->addGroup('journalLedgers')
                ->setEntityName(JournalLedger::class)
                ->addField('transactionDate')
                    ->addOperator(LessEqualType::class)
                        ->getInput(1)
                            ->setAttributes(array('data-pick' => 'date'))
                    ->setDefault(LessEqualType::class, new \DateTime())
        ;
    }

    public function buildData(DataBuilder $builder, ObjectRepository $repository, array $options)
    {
        $endDate = null;
        $builder->processSearch(function($values) use (&$endDate) {
            $endDate = $values[0];
        });
        
        $objects = $repository->getProfitLossData($endDate === null ? '' : $endDate->format('Y-m-d'));

        $builder->setData($objects);
    }
}
