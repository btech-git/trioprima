<?php

namespace AppBundle\Service\Report;

use LibBundle\Grid\DataGridView;
use AppBundle\Repository\Report\AccountJournalRepository;

class AccountJournalLedgerSummary
{
    private $journalLedgerRepository;
    
    public function __construct(AccountJournalRepository $journalLedgerRepository)
    {
        $this->journalLedgerRepository = $journalLedgerRepository;
    }
    
    public function getBeginningBalanceData(DataGridView $dataGridView)
    {
        $startDate = $dataGridView->searchVals['accountJournals']['transactionDate'][1];
        $beginningBalanceSummary = array();
        foreach ($dataGridView->data as $i => $account) {
            $beginningBalance = $this->journalLedgerRepository->getBeginningBalance($account, $startDate);
            $beginningBalanceSummary[$i] = $beginningBalance;
        }
        return $beginningBalanceSummary;
    }
}
