<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Transaction\PurchaseReturnHeader;
use AppBundle\Entity\Report\Inventory;
use AppBundle\Repository\Transaction\PurchaseReturnHeaderRepository;
use AppBundle\Repository\Report\InventoryRepository;

class PurchaseReturnForm
{
    private $purchaseReturnHeaderRepository;
    private $inventoryRepository;
    
    public function __construct(PurchaseReturnHeaderRepository $purchaseReturnHeaderRepository, InventoryRepository $inventoryRepository)
    {
        $this->purchaseReturnHeaderRepository = $purchaseReturnHeaderRepository;
        $this->inventoryRepository = $inventoryRepository;
    }
    
    public function initialize(PurchaseReturnHeader $purchaseReturnHeader, array $params = array())
    {
        list($month, $year, $staff) = array($params['month'], $params['year'], $params['staff']);
        
        if (empty($purchaseReturnHeader->getId())) {
            $lastPurchaseReturnHeader = $this->purchaseReturnHeaderRepository->findRecentBy($year, $month);
            $currentPurchaseReturnHeader = ($lastPurchaseReturnHeader === null) ? $purchaseReturnHeader : $lastPurchaseReturnHeader;
            $purchaseReturnHeader->setCodeNumberToNext($currentPurchaseReturnHeader->getCodeNumber(), $year, $month);
            
            $purchaseReturnHeader->setStaff($staff);
        }
    }
    
    public function finalize(PurchaseReturnHeader $purchaseReturnHeader, array $params = array())
    {
        foreach ($purchaseReturnHeader->getPurchaseReturnDetails() as $purchaseReturnDetail) {
            $purchaseReturnDetail->setPurchaseReturnHeader($purchaseReturnHeader);
        }
        $this->sync($purchaseReturnHeader);
    }
    
    private function sync(PurchaseReturnHeader $purchaseReturnHeader)
    {
        $purchaseInvoiceHeader = $purchaseReturnHeader->getPurchaseInvoiceHeader();
        if ($purchaseInvoiceHeader !== null) {
            $oldPurchaseReturnHeaders = $purchaseInvoiceHeader->getPurchaseReturnHeaders()->getValues();
            $purchaseReturnHeaders = new ArrayCollection($oldPurchaseReturnHeaders);
            if (!in_array($purchaseReturnHeader, $oldPurchaseReturnHeaders)) {
                $purchaseReturnHeaders->add($purchaseReturnHeader);
            }
            $totalReturn = 0.00;
            foreach ($purchaseReturnHeaders as $purchaseReturnHeader) {
                $totalReturn += $purchaseReturnHeader->getGrandTotal();
            }
            $purchaseInvoiceHeader->setTotalReturn($totalReturn);
        }
    }
    
    public function save(PurchaseReturnHeader $purchaseReturnHeader)
    {
        if (empty($purchaseReturnHeader->getId())) {
            ObjectPersister::save(function() use ($purchaseReturnHeader) {
                $this->purchaseReturnHeaderRepository->add($purchaseReturnHeader, array(
                    'purchaseReturnDetails' => array('add' => true),
                ));
                $this->markInventories($purchaseReturnHeader);
            });
        } else {
            ObjectPersister::save(function() use ($purchaseReturnHeader) {
                $this->purchaseReturnHeaderRepository->update($purchaseReturnHeader, array(
                    'purchaseReturnDetails' => array('add' => true, 'remove' => true),
                ));
                $this->markInventories($purchaseReturnHeader);
            });
        }
    }
    
    public function delete(PurchaseReturnHeader $purchaseReturnHeader)
    {
        $this->beforeDelete($purchaseReturnHeader);
        if (!empty($purchaseReturnHeader->getId())) {
            ObjectPersister::save(function() use ($purchaseReturnHeader) {
                $this->purchaseReturnHeaderRepository->remove($purchaseReturnHeader, array(
                    'purchaseReturnDetails' => array('remove' => true),
                ));
                $this->markInventories($purchaseReturnHeader);
            });
        }
    }
    
    protected function beforeDelete(PurchaseReturnHeader $purchaseReturnHeader)
    {
        $purchaseReturnHeader->getPurchaseReturnDetails()->clear();
        $purchaseReturnHeader->setIsTax(false);
        $purchaseReturnHeader->setShippingFee(0.00);
        $this->sync($purchaseReturnHeader);
    }
    
    private function markInventories(PurchaseReturnHeader $purchaseReturnHeader)
    {
        $oldInventories = $this->inventoryRepository->findBy(array(
            'transactionType' => Inventory::TRANSACTION_TYPE_PURCHASE_RETURN,
            'codeNumberYear' => $purchaseReturnHeader->getCodeNumberYear(),
            'codeNumberMonth' => $purchaseReturnHeader->getCodeNumberMonth(),
            'codeNumberOrdinal' => $purchaseReturnHeader->getCodeNumberOrdinal(),
        ));
        $this->inventoryRepository->remove($oldInventories);
        foreach ($purchaseReturnHeader->getPurchaseReturnDetails() as $purchaseReturnDetail) {
            if ($purchaseReturnDetail->getQuantity() > 0) {
                $inventory = new Inventory();
                $inventory->setCodeNumber($purchaseReturnHeader->getCodeNumber());
                $inventory->setTransactionDate($purchaseReturnHeader->getTransactionDate());
                $inventory->setTransactionType(Inventory::TRANSACTION_TYPE_PURCHASE_RETURN);
                $inventory->setTransactionSubject($purchaseReturnHeader->getPurchaseInvoiceHeader()->getSupplier()->getCompany());
                $inventory->setNote($purchaseReturnHeader->getNote());
                $inventory->setQuantityIn(0);
                $inventory->setQuantityOut($purchaseReturnDetail->getQuantity());
                $inventory->setUnitPrice($purchaseReturnDetail->getPurchaseInvoiceDetail()->getUnitPrice());
                $inventory->setProduct($purchaseReturnDetail->getPurchaseInvoiceDetail()->getProduct());
                $inventory->setStaff($purchaseReturnHeader->getStaff());
                $this->inventoryRepository->add($inventory);
            }
        }
    }
}
