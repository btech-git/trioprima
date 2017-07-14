<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use AppBundle\Entity\Transaction\PurchasePaymentHeader;
use AppBundle\Repository\Transaction\PurchasePaymentHeaderRepository;

class PurchasePaymentForm
{
    private $purchasePaymentHeaderRepository;
    
    public function __construct(PurchasePaymentHeaderRepository $purchasePaymentHeaderRepository)
    {
        $this->purchasePaymentHeaderRepository = $purchasePaymentHeaderRepository;
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
        }
    }
    
    public function save(PurchasePaymentHeader $purchasePaymentHeader)
    {
        if (empty($purchasePaymentHeader->getId())) {
            ObjectPersister::save(function() use ($purchasePaymentHeader) {
                $this->purchasePaymentHeaderRepository->add($purchasePaymentHeader, array(
                    'purchasePaymentDetails' => array('add' => true),
                ));
            });
        } else {
            ObjectPersister::save(function() use ($purchasePaymentHeader) {
                $this->purchasePaymentHeaderRepository->update($purchasePaymentHeader, array(
                    'purchasePaymentDetails' => array('add' => true, 'remove' => true),
                ));
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
            });
        }
    }
    
    protected function beforeDelete(PurchasePaymentHeader $purchasePaymentHeader)
    {
        $purchasePaymentHeader->getPurchasePaymentDetails()->clear();
        $this->sync($purchasePaymentHeader);
    }
}
