<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use AppBundle\Entity\Transaction\ExpenseHeader;
use AppBundle\Entity\Report\AccountJournal;
use AppBundle\Repository\Transaction\ExpenseHeaderRepository;
use AppBundle\Repository\Report\AccountJournalRepository;

class ExpenseForm
{
    private $expenseHeaderRepository;
    private $accountJournalRepository;
    
    public function __construct(ExpenseHeaderRepository $expenseHeaderRepository, AccountJournalRepository $accountJournalRepository)
    {
        $this->expenseHeaderRepository = $expenseHeaderRepository;
        $this->accountJournalRepository = $accountJournalRepository;
    }
    
    public function initialize(ExpenseHeader $expenseHeader, array $params = array())
    {
        list($month, $year, $staff) = array($params['month'], $params['year'], $params['staff']);
        
        if (empty($expenseHeader->getId())) {
            $lastExpenseHeader = $this->expenseHeaderRepository->findRecentBy($year, $month);
            $currentExpenseHeader = ($lastExpenseHeader === null) ? $expenseHeader : $lastExpenseHeader;
            $expenseHeader->setCodeNumberToNext($currentExpenseHeader->getCodeNumber(), $year, $month);
            
            $expenseHeader->setStaff($staff);
        }
    }
    
    public function finalize(ExpenseHeader $expenseHeader, array $params = array())
    {
        foreach ($expenseHeader->getExpenseDetails() as $expenseDetail) {
            $expenseDetail->setExpenseHeader($expenseHeader);
        }
    }
    
    public function save(ExpenseHeader $expenseHeader)
    {
        if (empty($expenseHeader->getId())) {
            ObjectPersister::save(function() use ($expenseHeader) {
                $this->expenseHeaderRepository->add($expenseHeader, array(
                    'expenseDetails' => array('add' => true),
                ));
                $this->markAccountJournals($expenseHeader, true);
            });
        } else {
            ObjectPersister::save(function() use ($expenseHeader) {
                $this->expenseHeaderRepository->update($expenseHeader, array(
                    'expenseDetails' => array('add' => true, 'remove' => true),
                ));
                $this->markAccountJournals($expenseHeader, true);
            });
        }
    }
    
    public function delete(ExpenseHeader $expenseHeader)
    {
        $this->beforeDelete($expenseHeader);
        if (!empty($expenseHeader->getId())) {
            ObjectPersister::save(function() use ($expenseHeader) {
                $this->expenseHeaderRepository->remove($expenseHeader, array(
                    'expenseDetails' => array('remove' => true),
                ));
                $this->markAccountJournals($expenseHeader, false);
            });
        }
    }
    
    protected function beforeDelete(ExpenseHeader $expenseHeader)
    {
        $expenseHeader->getExpenseDetails()->clear();
//        $this->sync($expenseHeader);
    }
    
    private function markAccountJournals(ExpenseHeader $expenseHeader, $addForHeader)
    {
        $oldAccountJournals = $this->accountJournalRepository->findBy(array(
            'transactionType' => AccountJournal::TRANSACTION_TYPE_EXPENSE,
            'codeNumberYear' => $expenseHeader->getCodeNumberYear(),
            'codeNumberMonth' => $expenseHeader->getCodeNumberMonth(),
            'codeNumberOrdinal' => $expenseHeader->getCodeNumberOrdinal(),
        ));
        $this->accountJournalRepository->remove($oldAccountJournals);
        foreach ($expenseHeader->getExpenseDetails() as $expenseDetail) {
            if ($expenseDetail->getAmount() > 0) {
                $accountJournal = new AccountJournal();
                $accountJournal->setCodeNumber($expenseHeader->getCodeNumber());
                $accountJournal->setTransactionDate($expenseHeader->getTransactionDate());
                $accountJournal->setTransactionType(AccountJournal::TRANSACTION_TYPE_EXPENSE);
                $accountJournal->setTransactionSubject($expenseDetail->getMemo());
                $accountJournal->setNote($expenseHeader->getNote());
                $accountJournal->setDebit($expenseDetail->getAmount());
                $accountJournal->setCredit(0);
                $accountJournal->setAccount($expenseDetail->getAccount());
                $accountJournal->setStaff($expenseHeader->getStaff());
                $this->accountJournalRepository->add($accountJournal);
            }
        }
        if ($addForHeader) {
            $accountJournal = new AccountJournal();
            $accountJournal->setCodeNumber($expenseHeader->getCodeNumber());
            $accountJournal->setTransactionDate($expenseHeader->getTransactionDate());
            $accountJournal->setTransactionType(AccountJournal::TRANSACTION_TYPE_EXPENSE);
            $accountJournal->setTransactionSubject('Expense');
            $accountJournal->setNote($expenseHeader->getNote());
            $accountJournal->setDebit(0);
            $accountJournal->setCredit($expenseHeader->getTotalAmount());
            $accountJournal->setAccount($expenseHeader->getAccount());
            $accountJournal->setStaff($expenseHeader->getStaff());
            $this->accountJournalRepository->add($accountJournal);
        }
    }
}
