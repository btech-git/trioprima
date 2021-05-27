<?php

namespace AppBundle\Repository\Report;

use LibBundle\Doctrine\EntityRepository;

class InventoryRepository extends EntityRepository
{
    public function getStockByProduct($product)
    {
        $query = $this->_em->createQuery('SELECT COALESCE(SUM(t.quantityIn - t.quantityOut), 0) AS stock FROM AppBundle\Entity\Report\Inventory t WHERE t.product = :product');
        $query->setParameter('product', $product);
        $stock = $query->getSingleScalarResult();
        
        return $stock;
    }
    
    public function getInventoryStockByProduct($product, $startDate, $endDate)
    {
        $query = $this->_em->createQuery('SELECT COALESCE(SUM(t.quantityIn - t.quantityOut), 0) AS stock FROM AppBundle\Entity\Report\Inventory t WHERE t.product = :product AND t.transactionDate BETWEEN :startDate AND :endDate');
        $query->setParameter('product', $product);
        $query->setParameter('startDate', $startDate);
        $query->setParameter('endDate', $endDate);
        $stock = $query->getSingleScalarResult();
        
        return $stock;
    }
    
    public function getTotalQuantityInByProduct($product, $startDate, $endDate)
    {
        $query = $this->_em->createQuery('SELECT COALESCE(SUM(t.quantityIn), 0) AS stock FROM AppBundle\Entity\Report\Inventory t WHERE t.product = :product AND t.transactionDate BETWEEN :startDate AND :endDate');
        $query->setParameter('product', $product);
        $query->setParameter('startDate', $startDate);
        $query->setParameter('endDate', $endDate);
        $stock = $query->getSingleScalarResult();
        
        return $stock;
    }
    
    public function getTotalQuantityOutByProduct($product, $startDate, $endDate)
    {
        $query = $this->_em->createQuery('SELECT COALESCE(SUM(t.quantityOut), 0) AS stock FROM AppBundle\Entity\Report\Inventory t WHERE t.product = :product AND t.transactionDate BETWEEN :startDate AND :endDate');
        $query->setParameter('product', $product);
        $query->setParameter('startDate', $startDate);
        $query->setParameter('endDate', $endDate);
        $stock = $query->getSingleScalarResult();
        
        return $stock;
    }
    
    public function getTotalPriceByProduct($product, $startDate, $endDate)
    {
        $query = $this->_em->createQuery('SELECT COALESCE(SUM(t.quantityIn * t.unitPrice) - SUM(t.quantityOut * t.unitPrice), 0)  AS stock FROM AppBundle\Entity\Report\Inventory t WHERE t.product = :product AND t.transactionDate BETWEEN :startDate AND :endDate');
        $query->setParameter('product', $product);
        $query->setParameter('startDate', $startDate);
        $query->setParameter('endDate', $endDate);
        $stock = $query->getSingleScalarResult();
        
        return $stock;
    }
}
