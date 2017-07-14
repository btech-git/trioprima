<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use AppBundle\Entity\Transaction\PurchaseInvoiceHeader;
use AppBundle\Entity\Report\Inventory;
use AppBundle\Repository\Transaction\PurchaseInvoiceHeaderRepository;
use AppBundle\Repository\Report\InventoryRepository;

class PurchaseInvoiceForm
{
    private $purchaseInvoiceHeaderRepository;
    private $inventoryRepository;
    
    public function __construct(PurchaseInvoiceHeaderRepository $purchaseInvoiceHeaderRepository, InventoryRepository $inventoryRepository)
    {
        $this->purchaseInvoiceHeaderRepository = $purchaseInvoiceHeaderRepository;
        $this->inventoryRepository = $inventoryRepository;
    }
    
    public function initialize(PurchaseInvoiceHeader $purchaseInvoiceHeader, array $params = array())
    {
        list($month, $year, $staff) = array($params['month'], $params['year'], $params['staff']);
        
        if (empty($purchaseInvoiceHeader->getId())) {
            $lastPurchaseInvoiceHeader = $this->purchaseInvoiceHeaderRepository->findRecentBy($year, $month);
            $currentPurchaseInvoiceHeader = ($lastPurchaseInvoiceHeader === null) ? $purchaseInvoiceHeader : $lastPurchaseInvoiceHeader;
            $purchaseInvoiceHeader->setCodeNumberToNext($currentPurchaseInvoiceHeader->getCodeNumber(), $year, $month);
            
            $purchaseInvoiceHeader->setStaffCreated($staff);
            $purchaseInvoiceHeader->setTotalPayment(0.00);
            $purchaseInvoiceHeader->setTotalReturn(0.00);
        }
    }
    
    public function finalize(PurchaseInvoiceHeader $purchaseInvoiceHeader, array $params = array())
    {
        foreach ($purchaseInvoiceHeader->getPurchaseInvoiceDetails() as $purchaseInvoiceDetail) {
            $purchaseInvoiceDetail->setPurchaseInvoiceHeader($purchaseInvoiceHeader);
        }
        $this->sync($purchaseInvoiceHeader);
    }
    
    private function sync(PurchaseInvoiceHeader $purchaseInvoiceHeader)
    {
        $purchaseInvoiceHeader->sync();
    }
    
    public function save(PurchaseInvoiceHeader $purchaseInvoiceHeader)
    {
        if (empty($purchaseInvoiceHeader->getId())) {
            ObjectPersister::save(function() use ($purchaseInvoiceHeader) {
                $this->purchaseInvoiceHeaderRepository->add($purchaseInvoiceHeader, array(
                    'purchaseInvoiceDetails' => array('add' => true),
                ));
                $this->markInventories($purchaseInvoiceHeader);
            });
        } else {
            ObjectPersister::save(function() use ($purchaseInvoiceHeader) {
                $this->purchaseInvoiceHeaderRepository->update($purchaseInvoiceHeader, array(
                    'purchaseInvoiceDetails' => array('add' => true, 'remove' => true),
                ));
                $this->markInventories($purchaseInvoiceHeader);
            });
        }
    }
    
    public function delete(PurchaseInvoiceHeader $purchaseInvoiceHeader)
    {
        $this->beforeDelete($purchaseInvoiceHeader);
        if (!empty($purchaseInvoiceHeader->getId())) {
            ObjectPersister::save(function() use ($purchaseInvoiceHeader) {
                $this->purchaseInvoiceHeaderRepository->remove($purchaseInvoiceHeader, array(
                    'purchaseInvoiceDetails' => array('remove' => true),
                ));
                $this->markInventories($purchaseInvoiceHeader);
            });
        }
    }
    
    public function isValidForDelete(PurchaseInvoiceHeader $purchaseInvoiceHeader)
    {
        $valid = $purchaseInvoiceHeader->getPurchasePaymentDetails()->isEmpty() && $purchaseInvoiceHeader->getPurchaseReceiptDetails()->isEmpty() && $purchaseInvoiceHeader->getPurchaseReturnHeaders()->isEmpty();
        if ($valid) {
            foreach ($purchaseInvoiceHeader->getPurchaseInvoiceDetails() as $purchaseInvoiceDetail) {
                $valid = $valid && $purchaseInvoiceDetail->getPurchaseReturnDetails()->isEmpty();
            }
        }
        
        return $valid;
    }
    
    protected function beforeDelete(PurchaseInvoiceHeader $purchaseInvoiceHeader)
    {
        $purchaseInvoiceHeader->getPurchaseInvoiceDetails()->clear();
        $this->sync($purchaseInvoiceHeader);
    }
    
    private function markInventories(PurchaseInvoiceHeader $purchaseInvoiceHeader)
    {
        $oldInventories = $this->inventoryRepository->findBy(array(
            'transactionType' => Inventory::TRANSACTION_TYPE_RECEIVE,
            'codeNumberYear' => $purchaseInvoiceHeader->getCodeNumberYear(),
            'codeNumberMonth' => $purchaseInvoiceHeader->getCodeNumberMonth(),
            'codeNumberOrdinal' => $purchaseInvoiceHeader->getCodeNumberOrdinal(),
        ));
        $this->inventoryRepository->remove($oldInventories);
        foreach ($purchaseInvoiceHeader->getPurchaseInvoiceDetails() as $purchaseInvoiceDetail) {
            if ($purchaseInvoiceDetail->getQuantity() > 0) {
                $inventory = new Inventory();
                $inventory->setCodeNumber($purchaseInvoiceHeader->getCodeNumber());
                $inventory->setTransactionDate($purchaseInvoiceHeader->getTransactionDate());
                $inventory->setTransactionType(Inventory::TRANSACTION_TYPE_RECEIVE);
                $inventory->setTransactionSubject($purchaseInvoiceHeader->getSupplier()->getCompany());
                $inventory->setNote($purchaseInvoiceHeader->getNote());
                $inventory->setQuantityIn($purchaseInvoiceDetail->getQuantity());
                $inventory->setQuantityOut(0);
                $inventory->setUnitPrice($purchaseInvoiceDetail->getUnitPrice());
                $inventory->setProduct($purchaseInvoiceDetail->getProduct());
                $inventory->setStaff($purchaseInvoiceHeader->getStaffCreated());
                $this->inventoryRepository->add($inventory);
            }
        }
    }
}
