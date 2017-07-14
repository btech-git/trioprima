<?php

namespace AppBundle\Repository\Transaction;

use LibBundle\Doctrine\EntityRepository;
use AppBundle\Entity\Transaction\SalePaymentHeader;
use AppBundle\Entity\Transaction\SalePaymentDetail;

class SalePaymentHeaderRepository extends EntityRepository
{
    public function findSalePaymentDetailsBy(SalePaymentHeader $salePaymentHeader)
    {
        $salePaymentDetails = $this->_em->getRepository(SalePaymentDetail::class)->findBySalePaymentHeader($salePaymentHeader);
        
        return $salePaymentDetails;
    }
    
    public function findRecentBy($year, $month)
    {
        $query = $this->_em->createQuery('SELECT t FROM AppBundle\Entity\Transaction\SalePaymentHeader t WHERE t.codeNumberMonth = :codeNumberMonth AND t.codeNumberYear = :codeNumberYear ORDER BY t.codeNumberOrdinal DESC');
        $query->setParameter('codeNumberMonth', $month);
        $query->setParameter('codeNumberYear', $year);
        $query->setMaxResults(1);
        $lastSalePaymentHeader = $query->getOneOrNullResult();
        
        return $lastSalePaymentHeader;
    }
}
