<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Transaction\SaleReturnHeader;
use AppBundle\Entity\Report\Inventory;
use AppBundle\Repository\Transaction\SaleReturnHeaderRepository;
use AppBundle\Repository\Report\InventoryRepository;

class SaleReturnForm
{
    private $saleReturnHeaderRepository;
    private $inventoryRepository;
    
    public function __construct(SaleReturnHeaderRepository $saleReturnHeaderRepository, InventoryRepository $inventoryRepository)
    {
        $this->saleReturnHeaderRepository = $saleReturnHeaderRepository;
        $this->inventoryRepository = $inventoryRepository;
    }
    
    public function initialize(SaleReturnHeader $saleReturnHeader, array $params = array())
    {
        list($month, $year, $staff) = array($params['month'], $params['year'], $params['staff']);
        
        if (empty($saleReturnHeader->getId())) {
            $lastSaleReturnHeader = $this->saleReturnHeaderRepository->findRecentBy($year, $month);
            $currentSaleReturnHeader = ($lastSaleReturnHeader === null) ? $saleReturnHeader : $lastSaleReturnHeader;
            $saleReturnHeader->setCodeNumberToNext($currentSaleReturnHeader->getCodeNumber(), $year, $month);
            
            $saleReturnHeader->setStaff($staff);
        }
    }
    
    public function finalize(SaleReturnHeader $saleReturnHeader, array $params = array())
    {
        foreach ($saleReturnHeader->getSaleReturnDetails() as $saleReturnDetail) {
            $saleReturnDetail->setSaleReturnHeader($saleReturnHeader);
        }
        $this->sync($saleReturnHeader);
    }
    
    private function sync(SaleReturnHeader $saleReturnHeader)
    {
        $saleInvoiceHeader = $saleReturnHeader->getSaleInvoiceHeader();
        if ($saleInvoiceHeader !== null) {
            $oldSaleReturnHeaders = $saleInvoiceHeader->getSaleReturnHeaders()->getValues();
            $saleReturnHeaders = new ArrayCollection($oldSaleReturnHeaders);
            if (!in_array($saleReturnHeader, $oldSaleReturnHeaders)) {
                $saleReturnHeaders->add($saleReturnHeader);
            }
            $totalReturn = 0.00;
            foreach ($saleReturnHeaders as $saleReturnHeader) {
                $totalReturn += $saleReturnHeader->getGrandTotal();
            }
            $saleInvoiceHeader->setTotalReturn($totalReturn);
        }
    }
    
    public function save(SaleReturnHeader $saleReturnHeader)
    {
        if (empty($saleReturnHeader->getId())) {
            ObjectPersister::save(function() use ($saleReturnHeader) {
                $this->saleReturnHeaderRepository->add($saleReturnHeader, array(
                    'saleReturnDetails' => array('add' => true),
                ));
                $this->markInventories($saleReturnHeader);
            });
        } else {
            ObjectPersister::save(function() use ($saleReturnHeader) {
                $this->saleReturnHeaderRepository->update($saleReturnHeader, array(
                    'saleReturnDetails' => array('add' => true, 'remove' => true),
                ));
                $this->markInventories($saleReturnHeader);
            });
        }
    }
    
    public function delete(SaleReturnHeader $saleReturnHeader)
    {
        $this->beforeDelete($saleReturnHeader);
        if (!empty($saleReturnHeader->getId())) {
            ObjectPersister::save(function() use ($saleReturnHeader) {
                $this->saleReturnHeaderRepository->remove($saleReturnHeader, array(
                    'saleReturnDetails' => array('remove' => true),
                ));
                $this->markInventories($saleReturnHeader);
            });
        }
    }
    
    protected function beforeDelete(SaleReturnHeader $saleReturnHeader)
    {
        $saleReturnHeader->getSaleReturnDetails()->clear();
        $saleReturnHeader->setIsTax(false);
        $saleReturnHeader->setShippingFee(0.00);
        $this->sync($saleReturnHeader);
    }
    
    private function markInventories(SaleReturnHeader $saleReturnHeader)
    {
        $oldInventories = $this->inventoryRepository->findBy(array(
            'transactionType' => Inventory::TRANSACTION_TYPE_SALE_RETURN,
            'codeNumberYear' => $saleReturnHeader->getCodeNumberYear(),
            'codeNumberMonth' => $saleReturnHeader->getCodeNumberMonth(),
            'codeNumberOrdinal' => $saleReturnHeader->getCodeNumberOrdinal(),
        ));
        $this->inventoryRepository->remove($oldInventories);
        foreach ($saleReturnHeader->getSaleReturnDetails() as $saleReturnDetail) {
            if ($saleReturnDetail->getQuantity() > 0) {
                $inventory = new Inventory();
                $inventory->setCodeNumber($saleReturnHeader->getCodeNumber());
                $inventory->setTransactionDate($saleReturnHeader->getTransactionDate());
                $inventory->setTransactionType(Inventory::TRANSACTION_TYPE_SALE_RETURN);
                $inventory->setTransactionSubject($saleReturnHeader->getSaleInvoiceHeader()->getCustomer()->getCompany());
                $inventory->setNote($saleReturnHeader->getNote());
                $inventory->setQuantityIn($saleReturnDetail->getQuantity());
                $inventory->setQuantityOut(0);
                $inventory->setUnitPrice($saleReturnDetail->getSaleInvoiceDetail()->getUnitPrice());
                $inventory->setProduct($saleReturnDetail->getSaleInvoiceDetail()->getProduct());
                $inventory->setStaff($saleReturnHeader->getStaff());
                $this->inventoryRepository->add($inventory);
            }
        }
    }
}
