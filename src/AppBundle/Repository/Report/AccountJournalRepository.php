<?php

namespace AppBundle\Repository\Report;

use LibBundle\Doctrine\EntityRepository;

class AccountJournalRepository extends EntityRepository
{
    public function getBeginningBalance($account, $startDate)
    {
        $query = $this->_em->createQuery('SELECT COALESCE(SUM(t.debit - t.credit), 0) AS beginningBalance FROM AppBundle\Entity\Report\AccountJournal t WHERE t.account = :account AND t.transactionDate < :transactionDate');
        $query->setParameter('account', $account);
        $query->setParameter('transactionDate', $startDate);
        $beginningBalance = $query->getSingleScalarResult();
        
        return $beginningBalance;
    }
}
