<?php

namespace AppBundle\Repository\Transaction;

use LibBundle\Doctrine\EntityRepository;

class PurchaseInvoiceDetailRepository extends EntityRepository
{
    public function getAveragePurchasePriceByProduct($product)
    {
//        $query = $this->_em->createQuery('SELECT COALESCE(SUM(t.unitPrice * t.quantity * (1 - t.discount / 100) * (CASE WHEN h.isTax = true THEN 1.1 ELSE 1 END)) / SUM(t.quantity), 0) AS average_purchase_price FROM AppBundle\Entity\Transaction\PurchaseInvoiceDetail t JOIN t.purchaseInvoiceHeader h WHERE t.product = :product');
        $query = $this->_em->createQuery('SELECT COALESCE(t.unitPrice * (1 - t.discount / 100) * (CASE WHEN h.isTax = true THEN 1.1 ELSE 1 END), 0) AS average_purchase_price FROM AppBundle\Entity\Transaction\PurchaseInvoiceDetail t JOIN t.purchaseInvoiceHeader h WHERE t.product = :product AND transactionDate > "2022-12-31" ORDER BY t.id DESC');
        $query->setParameter('product', $product);
        $query->setMaxResults(1);
        $lastPurchaseInvoiceDetailRow = $query->getOneOrNullResult();
        
        return $lastPurchaseInvoiceDetailRow === null ? '0.00' : $lastPurchaseInvoiceDetailRow['average_purchase_price'];
    }
}
