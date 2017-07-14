<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use AppBundle\Entity\Transaction\DepositHeader;
use AppBundle\Entity\Report\AccountJournal;
use AppBundle\Repository\Transaction\DepositHeaderRepository;
use AppBundle\Repository\Report\AccountJournalRepository;

class DepositForm
{
    private $depositHeaderRepository;
    private $accountJournalRepository;
    
    public function __construct(DepositHeaderRepository $depositHeaderRepository, AccountJournalRepository $accountJournalRepository)
    {
        $this->depositHeaderRepository = $depositHeaderRepository;
        $this->accountJournalRepository = $accountJournalRepository;
    }
    
    public function initialize(DepositHeader $depositHeader, array $params = array())
    {
        list($month, $year, $staff) = array($params['month'], $params['year'], $params['staff']);
        
        if (empty($depositHeader->getId())) {
            $lastDepositHeader = $this->depositHeaderRepository->findRecentBy($year, $month);
            $currentDepositHeader = ($lastDepositHeader === null) ? $depositHeader : $lastDepositHeader;
            $depositHeader->setCodeNumberToNext($currentDepositHeader->getCodeNumber(), $year, $month);
            
            $depositHeader->setStaff($staff);
        }
    }
    
    public function finalize(DepositHeader $depositHeader, array $params = array())
    {
        foreach ($depositHeader->getDepositDetails() as $depositDetail) {
            $depositDetail->setDepositHeader($depositHeader);
        }
    }
    
    public function save(DepositHeader $depositHeader)
    {
        if (empty($depositHeader->getId())) {
            ObjectPersister::save(function() use ($depositHeader) {
                $this->depositHeaderRepository->add($depositHeader, array(
                    'depositDetails' => array('add' => true),
                ));
                $this->markAccountJournals($depositHeader, true);
            });
        } else {
            ObjectPersister::save(function() use ($depositHeader) {
                $this->depositHeaderRepository->update($depositHeader, array(
                    'depositDetails' => array('add' => true, 'remove' => true),
                ));
                $this->markAccountJournals($depositHeader, true);
            });
        }
    }
    
    public function delete(DepositHeader $depositHeader)
    {
        $this->beforeDelete($depositHeader);
        if (!empty($depositHeader->getId())) {
            ObjectPersister::save(function() use ($depositHeader) {
                $this->depositHeaderRepository->remove($depositHeader, array(
                    'depositDetails' => array('remove' => true),
                ));
                $this->markAccountJournals($depositHeader, false);
            });
        }
    }
    
    protected function beforeDelete(DepositHeader $depositHeader)
    {
        $depositHeader->getDepositDetails()->clear();
//        $this->sync($depositHeader);
    }
    
    private function markAccountJournals(DepositHeader $depositHeader, $addForHeader)
    {
        $oldAccountJournals = $this->accountJournalRepository->findBy(array(
            'transactionType' => AccountJournal::TRANSACTION_TYPE_DEPOSIT,
            'codeNumberYear' => $depositHeader->getCodeNumberYear(),
            'codeNumberMonth' => $depositHeader->getCodeNumberMonth(),
            'codeNumberOrdinal' => $depositHeader->getCodeNumberOrdinal(),
        ));
        $this->accountJournalRepository->remove($oldAccountJournals);
        foreach ($depositHeader->getDepositDetails() as $depositDetail) {
            if ($depositDetail->getAmount() > 0) {
                $accountJournal = new AccountJournal();
                $accountJournal->setCodeNumber($depositHeader->getCodeNumber());
                $accountJournal->setTransactionDate($depositHeader->getTransactionDate());
                $accountJournal->setTransactionType(AccountJournal::TRANSACTION_TYPE_DEPOSIT);
                $accountJournal->setTransactionSubject($depositDetail->getMemo());
                $accountJournal->setNote($depositHeader->getNote());
                $accountJournal->setDebit(0);
                $accountJournal->setCredit($depositDetail->getAmount());
                $accountJournal->setAccount($depositDetail->getAccount());
                $accountJournal->setStaff($depositHeader->getStaff());
                $this->accountJournalRepository->add($accountJournal);
            }
        }
        if ($addForHeader) {
            $accountJournal = new AccountJournal();
            $accountJournal->setCodeNumber($depositHeader->getCodeNumber());
            $accountJournal->setTransactionDate($depositHeader->getTransactionDate());
            $accountJournal->setTransactionType(AccountJournal::TRANSACTION_TYPE_DEPOSIT);
            $accountJournal->setTransactionSubject('Deposit');
            $accountJournal->setNote($depositHeader->getNote());
            $accountJournal->setDebit($depositHeader->getTotalAmount());
            $accountJournal->setCredit(0);
            $accountJournal->setAccount($depositHeader->getAccount());
            $accountJournal->setStaff($depositHeader->getStaff());
            $this->accountJournalRepository->add($accountJournal);
        }
    }
}
