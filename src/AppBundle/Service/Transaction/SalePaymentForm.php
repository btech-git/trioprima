<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use AppBundle\Entity\Transaction\SalePaymentHeader;
use AppBundle\Entity\Report\AccountJournal;
use AppBundle\Repository\Transaction\SalePaymentHeaderRepository;
use AppBundle\Repository\Report\AccountJournalRepository;

class SalePaymentForm
{
    private $salePaymentHeaderRepository;
    private $accountJournalRepository;
    
    public function __construct(SalePaymentHeaderRepository $salePaymentHeaderRepository, AccountJournalRepository $accountJournalRepository)
    {
        $this->salePaymentHeaderRepository = $salePaymentHeaderRepository;
        $this->accountJournalRepository = $accountJournalRepository;
    }
    
    public function initialize(SalePaymentHeader $salePaymentHeader, array $params = array())
    {
        list($month, $year, $staff) = array($params['month'], $params['year'], $params['staff']);
        
        if (empty($salePaymentHeader->getId())) {
            $lastSalePaymentHeader = $this->salePaymentHeaderRepository->findRecentBy($year, $month);
            $currentSalePaymentHeader = ($lastSalePaymentHeader === null) ? $salePaymentHeader : $lastSalePaymentHeader;
            $salePaymentHeader->setCodeNumberToNext($currentSalePaymentHeader->getCodeNumber(), $year, $month);
            
            $salePaymentHeader->setStaff($staff);
        }
    }
    
    public function finalize(SalePaymentHeader $salePaymentHeader, array $params = array())
    {
        foreach ($salePaymentHeader->getSalePaymentDetails() as $salePaymentDetail) {
            $salePaymentDetail->setSalePaymentHeader($salePaymentHeader);
        }
        $this->sync($salePaymentHeader);
    }
    
    private function sync(SalePaymentHeader $salePaymentHeader)
    {
        $oldSalePaymentDetails = $this->salePaymentHeaderRepository->findSalePaymentDetailsBy($salePaymentHeader);
        $newSalePaymentDetails = $salePaymentHeader->getSalePaymentDetails()->getValues();
        $saleInvoiceHeaders = array();
        foreach ($oldSalePaymentDetails as $oldSalePaymentDetail) {
            $saleInvoiceHeaders[] = $oldSalePaymentDetail->getSaleInvoiceHeader();
        }
        foreach ($newSalePaymentDetails as $newSalePaymentDetail) {
            $saleInvoiceHeaders[] = $newSalePaymentDetail->getSaleInvoiceHeader();
        }
        $saleInvoiceHeaderIds = array();
        foreach ($saleInvoiceHeaders as $saleInvoiceHeader) {
            $saleInvoiceHeaderId = $saleInvoiceHeader->getId();
            if (in_array($saleInvoiceHeaderId, $saleInvoiceHeaderIds)) {
                continue;
            }
            $saleInvoiceHeaderIds[] = $saleInvoiceHeaderId;
            
            $salePaymentDetails = $saleInvoiceHeader->getSalePaymentDetails();
            foreach ($oldSalePaymentDetails as $oldSalePaymentDetail) {
                if (!in_array($oldSalePaymentDetail, $newSalePaymentDetails) && $oldSalePaymentDetail->getSaleInvoiceHeader()->getId() === $saleInvoiceHeaderId) {
                    $salePaymentDetails->removeElement($oldSalePaymentDetail);
                }
            }
            foreach ($newSalePaymentDetails as $newSalePaymentDetail) {
                if (!in_array($newSalePaymentDetail, $oldSalePaymentDetails) && $newSalePaymentDetail->getSaleInvoiceHeader()->getId() === $saleInvoiceHeaderId) {
                    $salePaymentDetails->add($newSalePaymentDetail);
                }
            }
            $totalPayment = 0.00;
            foreach ($salePaymentDetails as $salePaymentDetail) {
                $totalPayment += $salePaymentDetail->getAmount();
            }
            $saleInvoiceHeader->setTotalPayment($totalPayment);
            $saleInvoiceHeader->setRemaining($saleInvoiceHeader->getSyncRemaining());
        }
    }
    
    public function save(SalePaymentHeader $salePaymentHeader)
    {
        if (empty($salePaymentHeader->getId())) {
            ObjectPersister::save(function() use ($salePaymentHeader) {
                $this->salePaymentHeaderRepository->add($salePaymentHeader, array(
                    'salePaymentDetails' => array('add' => true),
                ));
                $this->markAccountJournals($salePaymentHeader, true);
            });
        } else {
            ObjectPersister::save(function() use ($salePaymentHeader) {
                $this->salePaymentHeaderRepository->update($salePaymentHeader, array(
                    'salePaymentDetails' => array('add' => true, 'remove' => true),
                ));
                $this->markAccountJournals($salePaymentHeader, true);
            });
        }
    }
    
    public function delete(SalePaymentHeader $salePaymentHeader)
    {
        $this->beforeDelete($salePaymentHeader);
        if (!empty($salePaymentHeader->getId())) {
            ObjectPersister::save(function() use ($salePaymentHeader) {
                $this->salePaymentHeaderRepository->remove($salePaymentHeader, array(
                    'salePaymentDetails' => array('remove' => true),
                ));
                $this->markAccountJournals($salePaymentHeader, false);
            });
        }
    }
    
    protected function beforeDelete(SalePaymentHeader $salePaymentHeader)
    {
        $salePaymentHeader->getSalePaymentDetails()->clear();
        $this->sync($salePaymentHeader);
    }
    
    private function markAccountJournals(SalePaymentHeader $salePaymentHeader, $addForHeader)
    {
        $oldAccountJournals = $this->accountJournalRepository->findBy(array(
            'transactionType' => AccountJournal::TRANSACTION_TYPE_SALE_PAYMENT,
            'codeNumberYear' => $salePaymentHeader->getCodeNumberYear(),
            'codeNumberMonth' => $salePaymentHeader->getCodeNumberMonth(),
            'codeNumberOrdinal' => $salePaymentHeader->getCodeNumberOrdinal(),
        ));
        $this->accountJournalRepository->remove($oldAccountJournals);
        foreach ($salePaymentHeader->getSalePaymentDetails() as $salePaymentDetail) {
            if ($salePaymentDetail->getAmount() > 0) {
                $accountJournal = new AccountJournal();
                $accountJournal->setCodeNumber($salePaymentHeader->getCodeNumber());
                $accountJournal->setTransactionDate($salePaymentHeader->getTransactionDate());
                $accountJournal->setTransactionType(AccountJournal::TRANSACTION_TYPE_SALE_PAYMENT);
                $accountJournal->setTransactionSubject($salePaymentDetail->getMemo());
                $accountJournal->setNote($salePaymentHeader->getNote());
                $accountJournal->setDebit($salePaymentDetail->getAmount());
                $accountJournal->setCredit(0);
                $accountJournal->setAccount($salePaymentDetail->getAccount());
                $accountJournal->setStaff($salePaymentHeader->getStaff());
                $this->accountJournalRepository->add($accountJournal);
            }
        }
        if ($addForHeader) {
            $accountJournal = new AccountJournal();
            $accountJournal->setCodeNumber($salePaymentHeader->getCodeNumber());
            $accountJournal->setTransactionDate($salePaymentHeader->getTransactionDate());
            $accountJournal->setTransactionType(AccountJournal::TRANSACTION_TYPE_SALE_PAYMENT);
            $accountJournal->setTransactionSubject($salePaymentHeader->getCustomer()->getCompany());
            $accountJournal->setNote($salePaymentHeader->getNote());
            $accountJournal->setDebit(0);
            $accountJournal->setCredit($salePaymentHeader->getTotalAmount());
            $accountJournal->setAccount($salePaymentHeader->getCustomer()->getAccount());
            $accountJournal->setStaff($salePaymentHeader->getStaff());
            $this->accountJournalRepository->add($accountJournal);
        }
    }
}
