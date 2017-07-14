<?php

namespace AppBundle\Repository\Transaction;

use LibBundle\Doctrine\EntityRepository;

class PurchaseInvoiceDetailRepository extends EntityRepository
{
    public function getAveragePurchasePriceByProduct($product)
    {
        $query = $this->_em->createQuery('SELECT COALESCE(SUM(t.unitPrice * t.quantity * (1 - t.discount / 100) * 1.1) / SUM(t.quantity), 0) AS average_purchase_price FROM AppBundle\Entity\Transaction\PurchaseInvoiceDetail t WHERE t.product = :product');
        $query->setParameter('product', $product);
        $averagePurchasePrice = $query->getSingleScalarResult();
        
        return $averagePurchasePrice;
    }
}
