<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use AppBundle\Entity\Transaction\PurchaseReceiptHeader;
use AppBundle\Repository\Transaction\PurchaseReceiptHeaderRepository;

class PurchaseReceiptForm
{
    private $purchaseReceiptHeaderRepository;
    
    public function __construct(PurchaseReceiptHeaderRepository $purchaseReceiptHeaderRepository)
    {
        $this->purchaseReceiptHeaderRepository = $purchaseReceiptHeaderRepository;
    }
    
    public function initialize(PurchaseReceiptHeader $purchaseReceiptHeader, array $params = array())
    {
        list($month, $year, $staff) = array($params['month'], $params['year'], $params['staff']);
        
        if (empty($purchaseReceiptHeader->getId())) {
            $lastPurchaseReceiptHeader = $this->purchaseReceiptHeaderRepository->findRecentBy($year, $month);
            $currentPurchaseReceiptHeader = ($lastPurchaseReceiptHeader === null) ? $purchaseReceiptHeader : $lastPurchaseReceiptHeader;
            $purchaseReceiptHeader->setCodeNumberToNext($currentPurchaseReceiptHeader->getCodeNumber(), $year, $month);
            
            $purchaseReceiptHeader->setStaff($staff);
        }
    }
    
    public function finalize(PurchaseReceiptHeader $purchaseReceiptHeader, array $params = array())
    {
        foreach ($purchaseReceiptHeader->getPurchaseReceiptDetails() as $purchaseReceiptDetail) {
            $purchaseReceiptDetail->setPurchaseReceiptHeader($purchaseReceiptHeader);
        }
    }
    
    public function save(PurchaseReceiptHeader $purchaseReceiptHeader)
    {
        if (empty($purchaseReceiptHeader->getId())) {
            ObjectPersister::save(function() use ($purchaseReceiptHeader) {
                $this->purchaseReceiptHeaderRepository->add($purchaseReceiptHeader, array(
                    'purchaseReceiptDetails' => array('add' => true),
                ));
            });
        } else {
            ObjectPersister::save(function() use ($purchaseReceiptHeader) {
                $this->purchaseReceiptHeaderRepository->update($purchaseReceiptHeader, array(
                    'purchaseReceiptDetails' => array('add' => true, 'remove' => true),
                ));
            });
        }
    }
    
    public function delete(PurchaseReceiptHeader $purchaseReceiptHeader)
    {
        $this->beforeDelete($purchaseReceiptHeader);
        if (!empty($purchaseReceiptHeader->getId())) {
            ObjectPersister::save(function() use ($purchaseReceiptHeader) {
                $this->purchaseReceiptHeaderRepository->remove($purchaseReceiptHeader, array(
                    'purchaseReceiptDetails' => array('remove' => true),
                ));
            });
        }
    }
    
    protected function beforeDelete(PurchaseReceiptHeader $purchaseReceiptHeader)
    {
        $purchaseReceiptHeader->getPurchaseReceiptDetails()->clear();
//        $this->sync($purchaseReceiptHeader);
    }
}
