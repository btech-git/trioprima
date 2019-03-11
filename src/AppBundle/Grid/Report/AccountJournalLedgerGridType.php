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
use LibBundle\Grid\SearchOperator\BetweenType;
use LibBundle\Grid\SearchOperator\ContainType;
use AppBundle\Entity\Master\Account;
use AppBundle\Entity\Report\AccountJournal;

class AccountJournalLedgerGridType extends DataGridType
{
    public function buildWidgets(WidgetsBuilder $builder, array $options)
    {
        $builder->searchWidget()
            ->addGroup('account')
                ->setEntityName(Account::class)
                ->addField('name')
                    ->addOperator(SearchBlankType::class)
                    ->addOperator(EqualType::class)
                    ->addOperator(ContainType::class)
            ->addGroup('accountJournals')
                ->setEntityName(AccountJournal::class)
                ->addField('transactionDate')
                    ->addOperator(BetweenType::class)
                        ->getInput(1)
                            ->setAttributes(array('data-pick' => 'date'))
                        ->getInput(2)
                            ->setAttributes(array('data-pick' => 'date'))
                    ->setDefault(BetweenType::class, new \DateTime(), new \DateTime())
        ;

        $builder->sortWidget()
            ->addGroup('account')
                ->addField('name')
                    ->addOperator(SortBlankType::class)
                    ->addOperator(AscendingType::class)
                    ->addOperator(DescendingType::class)
            ->addGroup('accountJournals')
                ->addField('transactionDate')
                    ->addOperator(SortBlankType::class)
                    ->addOperator(AscendingType::class)
                    ->setDefault(AscendingType::class)
        ;

        $builder->pageWidget()
            ->addPageSizeField()
                ->addItems(5, 10)
            ->addPageNumField()
        ;
    }

    public function buildData(DataBuilder $builder, ObjectRepository $repository, array $options)
    {
        list($criteria, $associations) = $this->getSpecifications($options);

        $builder->processSearch(function($values, $operator, $field, $group) use ($criteria, &$associations) {
            $operator::search($criteria[$group], $field, $values);
        });

        $builder->processSort(function($operator, $field, $group) use ($criteria) {
            $operator::sort($criteria[$group], $field);
        });

        $builder->processPage($repository->count($criteria['account'], $associations), function($offset, $size) use ($criteria) {
            $criteria['account']->setMaxResults($size);
            $criteria['account']->setFirstResult($offset);
        });
        
        $objects = $repository->match($criteria['account'], $associations);

        $builder->setData($objects);
    }

    private function getSpecifications(array $options)
    {
        $names = array('account', 'accountJournals');
        $criteria = array();
        foreach ($names as $name) {
            $criteria[$name] = Criteria::create();
        }

        $associations = array(
            'accountJournals' => array('criteria' => $criteria['accountJournals'], 'merge' => true),
        );

        return array($criteria, $associations);
    }
}
