<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use AppBundle\Entity\Transaction\PurchasePaymentHeader;
use AppBundle\Entity\Report\AccountJournal;
use AppBundle\Repository\Transaction\PurchasePaymentHeaderRepository;
use AppBundle\Repository\Report\AccountJournalRepository;

class PurchasePaymentForm
{
    private $purchasePaymentHeaderRepository;
    private $accountJournalRepository;
    
    public function __construct(PurchasePaymentHeaderRepository $purchasePaymentHeaderRepository, AccountJournalRepository $accountJournalRepository)
    {
        $this->purchasePaymentHeaderRepository = $purchasePaymentHeaderRepository;
        $this->accountJournalRepository = $accountJournalRepository;
    }
    
    public function initialize(PurchasePaymentHeader $purchasePaymentHeader, array $params = array())
    {
        list($month, $year, $staff) = array($params['month'], $params['year'], $params['staff']);
        
        if (empty($purchasePaymentHeader->getId())) {
            $lastPurchasePaymentHeader = $this->purchasePaymentHeaderRepository->findRecentBy($year, $month);
            $currentPurchasePaymentHeader = ($lastPurchasePaymentHeader === null) ? $purchasePaymentHeader : $lastPurchasePaymentHeader;
            $purchasePaymentHeader->setCodeNumberToNext($currentPurchasePaymentHeader->getCodeNumber(), $year, $month);
            
            $purchasePaymentHeader->setStaff($staff);
        }
    }
    
    public function finalize(PurchasePaymentHeader $purchasePaymentHeader, array $params = array())
    {
        foreach ($purchasePaymentHeader->getPurchasePaymentDetails() as $purchasePaymentDetail) {
            $purchasePaymentDetail->setPurchasePaymentHeader($purchasePaymentHeader);
        }
        $this->sync($purchasePaymentHeader);
    }
    
    private function sync(PurchasePaymentHeader $purchasePaymentHeader)
    {
        $oldPurchasePaymentDetails = $this->purchasePaymentHeaderRepository->findPurchasePaymentDetailsBy($purchasePaymentHeader);
        $newPurchasePaymentDetails = $purchasePaymentHeader->getPurchasePaymentDetails()->getValues();
        $purchaseInvoiceHeaders = array();
        foreach ($oldPurchasePaymentDetails as $oldPurchasePaymentDetail) {
            $purchaseInvoiceHeaders[] = $oldPurchasePaymentDetail->getPurchaseInvoiceHeader();
        }
        foreach ($newPurchasePaymentDetails as $newPurchasePaymentDetail) {
            $purchaseInvoiceHeaders[] = $newPurchasePaymentDetail->getPurchaseInvoiceHeader();
        }
        $purchaseInvoiceHeaderIds = array();
        foreach ($purchaseInvoiceHeaders as $purchaseInvoiceHeader) {
            $purchaseInvoiceHeaderId = $purchaseInvoiceHeader->getId();
            if (in_array($purchaseInvoiceHeaderId, $purchaseInvoiceHeaderIds)) {
                continue;
            }
            $purchaseInvoiceHeaderIds[] = $purchaseInvoiceHeaderId;
            
            $purchasePaymentDetails = $purchaseInvoiceHeader->getPurchasePaymentDetails();
            foreach ($oldPurchasePaymentDetails as $oldPurchasePaymentDetail) {
                if (!in_array($oldPurchasePaymentDetail, $newPurchasePaymentDetails) && $oldPurchasePaymentDetail->getPurchaseInvoiceHeader()->getId() === $purchaseInvoiceHeaderId) {
                    $purchasePaymentDetails->removeElement($oldPurchasePaymentDetail);
                }
            }
            foreach ($newPurchasePaymentDetails as $newPurchasePaymentDetail) {
                if (!in_array($newPurchasePaymentDetail, $oldPurchasePaymentDetails) && $newPurchasePaymentDetail->getPurchaseInvoiceHeader()->getId() === $purchaseInvoiceHeaderId) {
                    $purchasePaymentDetails->add($newPurchasePaymentDetail);
                }
            }
            $totalPayment = 0.00;
            foreach ($purchasePaymentDetails as $purchasePaymentDetail) {
                $totalPayment += $purchasePaymentDetail->getAmount();
            }
            $purchaseInvoiceHeader->setTotalPayment($totalPayment);
            $purchaseInvoiceHeader->setRemaining($purchaseInvoiceHeader->getSyncRemaining());
            if ($purchaseInvoiceHeader->remaining === 0) {
                $purchaseInvoiceHeader->setIsPaymentCompleted(true);
            }
        }
    }
    
    public function save(PurchasePaymentHeader $purchasePaymentHeader)
    {
        if (empty($purchasePaymentHeader->getId())) {
            ObjectPersister::save(function() use ($purchasePaymentHeader) {
                $this->purchasePaymentHeaderRepository->add($purchasePaymentHeader, array(
                    'purchasePaymentDetails' => array('add' => true),
                ));
                $this->markAccountJournals($purchasePaymentHeader, true);
            });
        } else {
            ObjectPersister::save(function() use ($purchasePaymentHeader) {
                $this->purchasePaymentHeaderRepository->update($purchasePaymentHeader, array(
                    'purchasePaymentDetails' => array('add' => true, 'remove' => true),
                ));
                $this->markAccountJournals($purchasePaymentHeader, true);
            });
        }
    }
    
    public function delete(PurchasePaymentHeader $purchasePaymentHeader)
    {
        $this->beforeDelete($purchasePaymentHeader);
        if (!empty($purchasePaymentHeader->getId())) {
            ObjectPersister::save(function() use ($purchasePaymentHeader) {
                $this->purchasePaymentHeaderRepository->remove($purchasePaymentHeader, array(
                    'purchasePaymentDetails' => array('remove' => true),
                ));
                $this->markAccountJournals($purchasePaymentHeader, true);
            });
        }
    }
    
    protected function beforeDelete(PurchasePaymentHeader $purchasePaymentHeader)
    {
        $purchasePaymentHeader->getPurchasePaymentDetails()->clear();
        $this->sync($purchasePaymentHeader);
    }
    
    private function markAccountJournals(PurchasePaymentHeader $purchasePaymentHeader, $addForHeader)
    {
        $oldAccountJournals = $this->accountJournalRepository->findBy(array(
            'transactionType' => AccountJournal::TRANSACTION_TYPE_PURCHASE_PAYMENT,
            'codeNumberYear' => $purchasePaymentHeader->getCodeNumberYear(),
            'codeNumberMonth' => $purchasePaymentHeader->getCodeNumberMonth(),
            'codeNumberOrdinal' => $purchasePaymentHeader->getCodeNumberOrdinal(),
        ));
        $this->accountJournalRepository->remove($oldAccountJournals);
        foreach ($purchasePaymentHeader->getPurchasePaymentDetails() as $purchasePaymentDetail) {
            if ($purchasePaymentDetail->getAmount() > 0) {
                $accountJournal = new AccountJournal();
                $accountJournal->setCodeNumber($purchasePaymentHeader->getCodeNumber());
                $accountJournal->setTransactionDate($purchasePaymentHeader->getTransactionDate());
                $accountJournal->setTransactionType(AccountJournal::TRANSACTION_TYPE_PURCHASE_PAYMENT);
                $accountJournal->setTransactionSubject($purchasePaymentDetail->getMemo());
                $accountJournal->setNote($purchasePaymentHeader->getNote());
                $accountJournal->setDebit($purchasePaymentDetail->getAmount());
                $accountJournal->setCredit(0);
                $accountJournal->setAccount($purchasePaymentDetail->getAccount());
                $accountJournal->setStaff($purchasePaymentHeader->getStaff());
                $this->accountJournalRepository->add($accountJournal);
            }
        }
        if ($addForHeader) {
            $accountJournal = new AccountJournal();
            $accountJournal->setCodeNumber($purchasePaymentHeader->getCodeNumber());
            $accountJournal->setTransactionDate($purchasePaymentHeader->getTransactionDate());
            $accountJournal->setTransactionType(AccountJournal::TRANSACTION_TYPE_PURCHASE_PAYMENT);
            $accountJournal->setTransactionSubject($purchasePaymentHeader->getSupplier()->getCompany());
            $accountJournal->setNote($purchasePaymentHeader->getNote());
            $accountJournal->setDebit(0);
            $accountJournal->setCredit($purchasePaymentHeader->getTotalAmount());
            $accountJournal->setAccount($purchasePaymentHeader->getSupplier()->getAccount());
            $accountJournal->setStaff($purchasePaymentHeader->getStaff());
            $this->accountJournalRepository->add($accountJournal);
        }
    }
}
