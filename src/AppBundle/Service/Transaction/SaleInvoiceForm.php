<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use AppBundle\Entity\Transaction\SaleInvoiceHeader;
use AppBundle\Entity\Report\Inventory;
use AppBundle\Repository\Transaction\SaleInvoiceHeaderRepository;
use AppBundle\Repository\Transaction\PurchaseInvoiceDetailRepository;
use AppBundle\Repository\Report\InventoryRepository;

class SaleInvoiceForm
{
    private $saleInvoiceHeaderRepository;
    private $purchaseInvoiceDetailRepository;
    private $inventoryRepository;
    
    public function __construct(SaleInvoiceHeaderRepository $saleInvoiceHeaderRepository, PurchaseInvoiceDetailRepository $purchaseInvoiceDetailRepository, InventoryRepository $inventoryRepository)
    {
        $this->saleInvoiceHeaderRepository = $saleInvoiceHeaderRepository;
        $this->purchaseInvoiceDetailRepository = $purchaseInvoiceDetailRepository;
        $this->inventoryRepository = $inventoryRepository;
    }
    
    public function initialize(SaleInvoiceHeader $saleInvoiceHeader, array $params = array())
    {
        list($month, $year, $taxInvoiceCode, $staff) = array($params['month'], $params['year'], $params['taxInvoiceCode'], $params['staff']);
        
        if (empty($saleInvoiceHeader->getId())) {
//            $lastSaleInvoiceHeader = $this->saleInvoiceHeaderRepository->findRecentBy($year, $month);
//            $currentSaleInvoiceHeader = ($lastSaleInvoiceHeader === null) ? $saleInvoiceHeader : $lastSaleInvoiceHeader;
//            $saleInvoiceHeader->setCodeNumberToNext($currentSaleInvoiceHeader->getCodeNumber(), $year, $month);
            
            $saleInvoiceHeader->setTaxInvoiceCode($taxInvoiceCode);
            $saleInvoiceHeader->setStaffCreated($staff);
            $saleInvoiceHeader->setTotalPayment(0.00);
            $saleInvoiceHeader->setTotalReturn(0.00);
        }
    }
    
    public function finalize(SaleInvoiceHeader $saleInvoiceHeader, array $params = array())
    {
        foreach ($saleInvoiceHeader->getSaleInvoiceDetails() as $saleInvoiceDetail) {
            $saleInvoiceDetail->setSaleInvoiceHeader($saleInvoiceHeader);
        }
//        if (empty($saleInvoiceHeader->getId())) {
            foreach ($saleInvoiceHeader->getSaleInvoiceDetails() as $saleInvoiceDetail) {
                $averagePurchasePrice = $this->purchaseInvoiceDetailRepository->getAveragePurchasePriceByProduct($saleInvoiceDetail->getProduct());
                $saleInvoiceDetail->setAveragePurchasePrice($averagePurchasePrice);
            }
//        }
        $this->sync($saleInvoiceHeader);
    }
    
    private function sync(SaleInvoiceHeader $saleInvoiceHeader)
    {
        $saleInvoiceHeader->sync();
    }
    
    public function save(SaleInvoiceHeader $saleInvoiceHeader)
    {
        if (empty($saleInvoiceHeader->getId())) {
            ObjectPersister::save(function() use ($saleInvoiceHeader) {
                $this->saleInvoiceHeaderRepository->add($saleInvoiceHeader, array(
                    'saleInvoiceDetails' => array('add' => true),
                ));
                $this->markInventories($saleInvoiceHeader);
            });
        } else {
            ObjectPersister::save(function() use ($saleInvoiceHeader) {
                $this->saleInvoiceHeaderRepository->update($saleInvoiceHeader, array(
                    'saleInvoiceDetails' => array('add' => true, 'remove' => true),
                ));
                $this->markInventories($saleInvoiceHeader);
            });
        }
    }
    
    public function delete(SaleInvoiceHeader $saleInvoiceHeader)
    {
        $this->beforeDelete($saleInvoiceHeader);
        if (!empty($saleInvoiceHeader->getId())) {
            ObjectPersister::save(function() use ($saleInvoiceHeader) {
                $this->saleInvoiceHeaderRepository->remove($saleInvoiceHeader, array(
                    'saleInvoiceDetails' => array('remove' => true),
                ));
                $this->markInventories($saleInvoiceHeader);
            });
        }
    }
    
    public function isValidForDelete(SaleInvoiceHeader $saleInvoiceHeader)
    {
        $valid = $saleInvoiceHeader->getSalePaymentDetails()->isEmpty() && $saleInvoiceHeader->getSaleReceiptDetails()->isEmpty() && $saleInvoiceHeader->getSaleReturnHeaders()->isEmpty();
        if ($valid) {
            foreach ($saleInvoiceHeader->getSaleInvoiceDetails() as $saleInvoiceDetail) {
                $valid = $valid && $saleInvoiceDetail->getSaleReturnDetails()->isEmpty();
            }
        }
        
        return $valid;
    }
    
    protected function beforeDelete(SaleInvoiceHeader $saleInvoiceHeader)
    {
        $saleInvoiceHeader->getSaleInvoiceDetails()->clear();
        $this->sync($saleInvoiceHeader);
    }
    
    private function markInventories(SaleInvoiceHeader $saleInvoiceHeader)
    {
        $oldInventories = $this->inventoryRepository->findBy(array(
            'transactionType' => Inventory::TRANSACTION_TYPE_DELIVERY,
            'codeNumberYear' => $saleInvoiceHeader->getCodeNumberYear(),
            'codeNumberMonth' => $saleInvoiceHeader->getCodeNumberMonth(),
            'codeNumberOrdinal' => $saleInvoiceHeader->getCodeNumberOrdinal(),
        ));
        $this->inventoryRepository->remove($oldInventories);
        foreach ($saleInvoiceHeader->getSaleInvoiceDetails() as $saleInvoiceDetail) {
            if ($saleInvoiceDetail->getQuantity() > 0) {
                $inventory = new Inventory();
                $inventory->setCodeNumber($saleInvoiceHeader->getCodeNumber());
                $inventory->setTransactionDate($saleInvoiceHeader->getTransactionDate());
                $inventory->setTransactionType(Inventory::TRANSACTION_TYPE_DELIVERY);
                $inventory->setTransactionSubject($saleInvoiceHeader->getCustomer()->getCompany());
                $inventory->setNote($saleInvoiceHeader->getNote());
                $inventory->setQuantityIn(0);
                $inventory->setQuantityOut($saleInvoiceDetail->getQuantity());
                $inventory->setUnitPrice($saleInvoiceDetail->getUnitPrice());
                $inventory->setProduct($saleInvoiceDetail->getProduct());
                $inventory->setStaff($saleInvoiceHeader->getStaffCreated());
                $this->inventoryRepository->add($inventory);
            }
        }
    }
}
