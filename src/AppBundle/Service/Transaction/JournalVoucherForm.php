<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use AppBundle\Entity\Transaction\JournalVoucherHeader;
use AppBundle\Entity\Report\AccountJournal;
use AppBundle\Repository\Transaction\JournalVoucherHeaderRepository;
use AppBundle\Repository\Report\AccountJournalRepository;

class JournalVoucherForm
{
    private $journalVoucherHeaderRepository;
    private $accountJournalRepository;
    
    public function __construct(JournalVoucherHeaderRepository $journalVoucherHeaderRepository, AccountJournalRepository $accountJournalRepository)
    {
        $this->journalVoucherHeaderRepository = $journalVoucherHeaderRepository;
        $this->accountJournalRepository = $accountJournalRepository;
    }
    
    public function initialize(JournalVoucherHeader $journalVoucherHeader, array $params = array())
    {
        list($month, $year, $staff) = array($params['month'], $params['year'], $params['staff']);
        
        if (empty($journalVoucherHeader->getId())) {
            $lastJournalVoucherHeader = $this->journalVoucherHeaderRepository->findRecentBy($year, $month);
            $currentJournalVoucherHeader = ($lastJournalVoucherHeader === null) ? $journalVoucherHeader : $lastJournalVoucherHeader;
            $journalVoucherHeader->setCodeNumberToNext($currentJournalVoucherHeader->getCodeNumber(), $year, $month);
            
            $journalVoucherHeader->setStaff($staff);
        }
    }
    
    public function finalize(JournalVoucherHeader $journalVoucherHeader, array $params = array())
    {
        foreach ($journalVoucherHeader->getJournalVoucherDetails() as $journalVoucherDetail) {
            $journalVoucherDetail->setJournalVoucherHeader($journalVoucherHeader);
        }
    }
    
    public function save(JournalVoucherHeader $journalVoucherHeader)
    {
        if (empty($journalVoucherHeader->getId())) {
            ObjectPersister::save(function() use ($journalVoucherHeader) {
                $this->journalVoucherHeaderRepository->add($journalVoucherHeader, array(
                    'journalVoucherDetails' => array('add' => true),
                ));
                $this->markAccountJournals($journalVoucherHeader);
            });
        } else {
            ObjectPersister::save(function() use ($journalVoucherHeader) {
                $this->journalVoucherHeaderRepository->update($journalVoucherHeader, array(
                    'journalVoucherDetails' => array('add' => true, 'remove' => true),
                ));
                $this->markAccountJournals($journalVoucherHeader);
            });
        }
    }
    
    public function delete(JournalVoucherHeader $journalVoucherHeader)
    {
        $this->beforeDelete($journalVoucherHeader);
        if (!empty($journalVoucherHeader->getId())) {
            ObjectPersister::save(function() use ($journalVoucherHeader) {
                $this->journalVoucherHeaderRepository->remove($journalVoucherHeader, array(
                    'journalVoucherDetails' => array('remove' => true),
                ));
                $this->markAccountJournals($journalVoucherHeader);
            });
        }
    }
    
    protected function beforeDelete(JournalVoucherHeader $journalVoucherHeader)
    {
        $journalVoucherHeader->getJournalVoucherDetails()->clear();
//        $this->sync($journalVoucherHeader);
    }
    
    private function markAccountJournals(JournalVoucherHeader $journalVoucherHeader)
    {
        $oldAccountJournals = $this->accountJournalRepository->findBy(array(
            'transactionType' => AccountJournal::TRANSACTION_TYPE_VOUCHER,
            'codeNumberYear' => $journalVoucherHeader->getCodeNumberYear(),
            'codeNumberMonth' => $journalVoucherHeader->getCodeNumberMonth(),
            'codeNumberOrdinal' => $journalVoucherHeader->getCodeNumberOrdinal(),
        ));
        $this->accountJournalRepository->remove($oldAccountJournals);
        foreach ($journalVoucherHeader->getJournalVoucherDetails() as $journalVoucherDetail) {
            if ($journalVoucherDetail->getDebit() > 0 || $journalVoucherDetail->getCredit() > 0) {
                $accountJournal = new AccountJournal();
                $accountJournal->setCodeNumber($journalVoucherHeader->getCodeNumber());
                $accountJournal->setTransactionDate($journalVoucherHeader->getTransactionDate());
                $accountJournal->setTransactionType(AccountJournal::TRANSACTION_TYPE_VOUCHER);
                $accountJournal->setTransactionSubject($journalVoucherDetail->getMemo());
                $accountJournal->setNote($journalVoucherHeader->getNote());
                $accountJournal->setDebit($journalVoucherDetail->getDebit());
                $accountJournal->setCredit($journalVoucherDetail->getCredit());
                $accountJournal->setAccount($journalVoucherDetail->getAccount());
                $accountJournal->setStaff($journalVoucherHeader->getStaff());
                $this->accountJournalRepository->add($accountJournal);
            }
        }
    }
}
