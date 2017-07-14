<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use AppBundle\Entity\Transaction\SaleReceiptHeader;
use AppBundle\Repository\Transaction\SaleReceiptHeaderRepository;

class SaleReceiptForm
{
    private $saleReceiptHeaderRepository;
    
    public function __construct(SaleReceiptHeaderRepository $saleReceiptHeaderRepository)
    {
        $this->saleReceiptHeaderRepository = $saleReceiptHeaderRepository;
    }
    
    public function initialize(SaleReceiptHeader $saleReceiptHeader, array $params = array())
    {
        list($month, $year, $staff) = array($params['month'], $params['year'], $params['staff']);
        
        if (empty($saleReceiptHeader->getId())) {
            $lastSaleReceiptHeader = $this->saleReceiptHeaderRepository->findRecentBy($year, $month);
            $currentSaleReceiptHeader = ($lastSaleReceiptHeader === null) ? $saleReceiptHeader : $lastSaleReceiptHeader;
            $saleReceiptHeader->setCodeNumberToNext($currentSaleReceiptHeader->getCodeNumber(), $year, $month);
            
            $saleReceiptHeader->setStaff($staff);
        }
    }
    
    public function finalize(SaleReceiptHeader $saleReceiptHeader, array $params = array())
    {
        foreach ($saleReceiptHeader->getSaleReceiptDetails() as $saleReceiptDetail) {
            $saleReceiptDetail->setSaleReceiptHeader($saleReceiptHeader);
        }
    }
    
    public function save(SaleReceiptHeader $saleReceiptHeader)
    {
        if (empty($saleReceiptHeader->getId())) {
            ObjectPersister::save(function() use ($saleReceiptHeader) {
                $this->saleReceiptHeaderRepository->add($saleReceiptHeader, array(
                    'saleReceiptDetails' => array('add' => true),
                ));
            });
        } else {
            ObjectPersister::save(function() use ($saleReceiptHeader) {
                $this->saleReceiptHeaderRepository->update($saleReceiptHeader, array(
                    'saleReceiptDetails' => array('add' => true, 'remove' => true),
                ));
            });
        }
    }
    
    public function delete(SaleReceiptHeader $saleReceiptHeader)
    {
        $this->beforeDelete($saleReceiptHeader);
        if (!empty($saleReceiptHeader->getId())) {
            ObjectPersister::save(function() use ($saleReceiptHeader) {
                $this->saleReceiptHeaderRepository->remove($saleReceiptHeader, array(
                    'saleReceiptDetails' => array('remove' => true),
                ));
            });
        }
    }
    
    protected function beforeDelete(SaleReceiptHeader $saleReceiptHeader)
    {
        $saleReceiptHeader->getSaleReceiptDetails()->clear();
//        $this->sync($saleReceiptHeader);
    }
}
