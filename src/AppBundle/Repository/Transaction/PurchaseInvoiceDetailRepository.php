<?php

namespace AppBundle\Repository\Transaction;

use LibBundle\Doctrine\EntityRepository;

class PurchaseInvoiceDetailRepository extends EntityRepository
{
    public function getAveragePurchasePriceByProduct($product)
    {
        $query = $this->_em->createQuery('SELECT t.unitPrice * (CASE WHEN h.isTax = true THEN 1.1 ELSE 1 END) AS average_purchase_price FROM AppBundle\Entity\Transaction\PurchaseInvoiceDetail t JOIN t.purchaseInvoiceHeader h WHERE t.product = :product ORDER BY t.id DESC');
        $query->setParameter('product', $product);
        $query->setMaxResults(1);
        $averagePurchasePrice = $query->getSingleScalarResult();
        dump($averagePurchasePrice);
        return $averagePurchasePrice;
    }
}
