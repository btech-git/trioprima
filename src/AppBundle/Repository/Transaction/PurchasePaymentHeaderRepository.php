<?php

namespace AppBundle\Repository\Transaction;

use LibBundle\Doctrine\EntityRepository;
use AppBundle\Entity\Transaction\PurchasePaymentHeader;
use AppBundle\Entity\Transaction\PurchasePaymentDetail;

class PurchasePaymentHeaderRepository extends EntityRepository
{
    public function findPurchasePaymentDetailsBy(PurchasePaymentHeader $purchasePaymentHeader)
    {
        $purchasePaymentDetails = $this->_em->getRepository(PurchasePaymentDetail::class)->findByPurchasePaymentHeader($purchasePaymentHeader);
        
        return $purchasePaymentDetails;
    }
    
    public function findRecentBy($year, $month)
    {
        $query = $this->_em->createQuery('SELECT t FROM AppBundle\Entity\Transaction\PurchasePaymentHeader t WHERE t.codeNumberMonth = :codeNumberMonth AND t.codeNumberYear = :codeNumberYear ORDER BY t.codeNumberOrdinal DESC');
        $query->setParameter('codeNumberMonth', $month);
        $query->setParameter('codeNumberYear', $year);
        $query->setMaxResults(1);
        $lastPurchasePaymentHeader = $query->getOneOrNullResult();
        
        return $lastPurchasePaymentHeader;
    }
}
