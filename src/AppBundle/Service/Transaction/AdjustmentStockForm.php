<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use AppBundle\Entity\Transaction\AdjustmentStockHeader;
use AppBundle\Entity\Report\Inventory;
use AppBundle\Repository\Transaction\AdjustmentStockHeaderRepository;
use AppBundle\Repository\Report\InventoryRepository;

class AdjustmentStockForm
{
    private $adjustmentStockHeaderRepository;
    private $inventoryRepository;
    
    public function __construct(AdjustmentStockHeaderRepository $adjustmentStockHeaderRepository, InventoryRepository $inventoryRepository)
    {
        $this->adjustmentStockHeaderRepository = $adjustmentStockHeaderRepository;
        $this->inventoryRepository = $inventoryRepository;
    }
    
    public function initialize(AdjustmentStockHeader $adjustmentStockHeader, array $params = array())
    {
        list($month, $year, $staff) = array($params['month'], $params['year'], $params['staff']);
        
        if (empty($adjustmentStockHeader->getId())) {
            $lastAdjustmentStockHeader = $this->adjustmentStockHeaderRepository->findRecentBy($year, $month);
            $currentAdjustmentStockHeader = ($lastAdjustmentStockHeader === null) ? $adjustmentStockHeader : $lastAdjustmentStockHeader;
            $adjustmentStockHeader->setCodeNumberToNext($currentAdjustmentStockHeader->getCodeNumber(), $year, $month);
            
            $adjustmentStockHeader->setStaff($staff);
        }
    }
    
    public function finalize(AdjustmentStockHeader $adjustmentStockHeader, array $params = array())
    {
        foreach ($adjustmentStockHeader->getAdjustmentStockDetails() as $adjustmentStockDetail) {
            $adjustmentStockDetail->setAdjustmentStockHeader($adjustmentStockHeader);
        }
        if (empty($adjustmentStockHeader->getId())) {
            foreach ($adjustmentStockHeader->getAdjustmentStockDetails() as $adjustmentStockDetail) {
                $stock = $this->inventoryRepository->getStockByProduct($adjustmentStockDetail->getProduct());
                $adjustmentStockDetail->setQuantityCurrent($stock);
            }
        }
    }
    
    public function save(AdjustmentStockHeader $adjustmentStockHeader)
    {
        if (empty($adjustmentStockHeader->getId())) {
            ObjectPersister::save(function() use ($adjustmentStockHeader) {
                $this->adjustmentStockHeaderRepository->add($adjustmentStockHeader, array(
                    'adjustmentStockDetails' => array('add' => true),
                ));
                $this->markInventories($adjustmentStockHeader);
            });
        } else {
            ObjectPersister::save(function() use ($adjustmentStockHeader) {
                $this->adjustmentStockHeaderRepository->update($adjustmentStockHeader, array(
                    'adjustmentStockDetails' => array('add' => true, 'remove' => true),
                ));
                $this->markInventories($adjustmentStockHeader);
            });
        }
    }
    
    public function delete(AdjustmentStockHeader $adjustmentStockHeader)
    {
        $this->beforeDelete($adjustmentStockHeader);
        if (!empty($adjustmentStockHeader->getId())) {
            ObjectPersister::save(function() use ($adjustmentStockHeader) {
                $this->adjustmentStockHeaderRepository->remove($adjustmentStockHeader, array(
                    'adjustmentStockDetails' => array('remove' => true),
                ));
                $this->markInventories($adjustmentStockHeader);
            });
        }
    }
    
    protected function beforeDelete(AdjustmentStockHeader $adjustmentStockHeader)
    {
        $adjustmentStockHeader->getAdjustmentStockDetails()->clear();
//        $this->sync($adjustmentStockHeader);
    }
    
    private function markInventories(AdjustmentStockHeader $adjustmentStockHeader)
    {
        $oldInventories = $this->inventoryRepository->findBy(array(
            'transactionType' => Inventory::TRANSACTION_TYPE_ADJUSTMENT,
            'codeNumberYear' => $adjustmentStockHeader->getCodeNumberYear(),
            'codeNumberMonth' => $adjustmentStockHeader->getCodeNumberMonth(),
            'codeNumberOrdinal' => $adjustmentStockHeader->getCodeNumberOrdinal(),
        ));
        $this->inventoryRepository->remove($oldInventories);
        foreach ($adjustmentStockHeader->getAdjustmentStockDetails() as $adjustmentStockDetail) {
            $inventory = new Inventory();
            $inventory->setCodeNumber($adjustmentStockHeader->getCodeNumber());
            $inventory->setTransactionDate($adjustmentStockHeader->getTransactionDate());
            $inventory->setTransactionType(Inventory::TRANSACTION_TYPE_ADJUSTMENT);
            $inventory->setTransactionSubject('Adjustment');
            $inventory->setNote($adjustmentStockHeader->getNote());
            if ($adjustmentStockDetail->getQuantityDifference() > 0) {
                $inventory->setQuantityIn(+$adjustmentStockDetail->getQuantityDifference());
                $inventory->setQuantityOut(0);
            } else {
                $inventory->setQuantityIn(0);
                $inventory->setQuantityOut(-$adjustmentStockDetail->getQuantityDifference());
            }
            $inventory->setUnitPrice(0.00);
            $inventory->setProduct($adjustmentStockDetail->getProduct());
            $inventory->setStaff($adjustmentStockHeader->getStaff());
            $this->inventoryRepository->add($inventory);
        }
    }
}
