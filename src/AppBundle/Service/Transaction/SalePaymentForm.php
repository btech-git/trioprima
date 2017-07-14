<?php

namespace AppBundle\Service\Transaction;

use LibBundle\Doctrine\ObjectPersister;
use AppBundle\Entity\Transaction\SalePaymentHeader;
use AppBundle\Repository\Transaction\SalePaymentHeaderRepository;

class SalePaymentForm
{
    private $salePaymentHeaderRepository;
    
    public function __construct(SalePaymentHeaderRepository $salePaymentHeaderRepository)
    {
        $this->salePaymentHeaderRepository = $salePaymentHeaderRepository;
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
        }
    }
    
    public function save(SalePaymentHeader $salePaymentHeader)
    {
        if (empty($salePaymentHeader->getId())) {
            ObjectPersister::save(function() use ($salePaymentHeader) {
                $this->salePaymentHeaderRepository->add($salePaymentHeader, array(
                    'salePaymentDetails' => array('add' => true),
                ));
            });
        } else {
            ObjectPersister::save(function() use ($salePaymentHeader) {
                $this->salePaymentHeaderRepository->update($salePaymentHeader, array(
                    'salePaymentDetails' => array('add' => true, 'remove' => true),
                ));
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
            });
        }
    }
    
    protected function beforeDelete(SalePaymentHeader $salePaymentHeader)
    {
        $salePaymentHeader->getSalePaymentDetails()->clear();
        $this->sync($salePaymentHeader);
    }
}
