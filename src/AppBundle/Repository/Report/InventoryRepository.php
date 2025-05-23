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
    
    public function getUnitPriceByProduct($product)
    {
        $query = $this->_em->createQuery('SELECT COALESCE(SUM(t.quantityIn * t.unitPrice) / SUM(t.quantityIn), 0) AS stock FROM AppBundle\Entity\Report\Inventory t WHERE t.product = :product AND t.quantityOut = 0');
        $query->setParameter('product', $product);
        $stock = $query->getSingleScalarResult();
        
        return $stock;
    }
    
    public function getQuantityInByProduct($product)
    {
        $query = $this->_em->createQuery('SELECT COALESCE(SUM(t.quantityIn), 0) AS stock FROM AppBundle\Entity\Report\Inventory t WHERE t.product = :product');
        $query->setParameter('product', $product);
        $stock = $query->getSingleScalarResult();
        
        return $stock;
    }
    
    public function getQuantityOutByProduct($product)
    {
        $query = $this->_em->createQuery('SELECT COALESCE(SUM(t.quantityOut), 0) AS stock FROM AppBundle\Entity\Report\Inventory t WHERE t.product = :product');
        $query->setParameter('product', $product);
        $stock = $query->getSingleScalarResult();
        
        return $stock;
    }
    
    public function getTotalPriceByProduct($product)
    {
        $query = $this->_em->createQuery('SELECT COALESCE(SUM(t.quantityIn - t.quantityOut) * (SUM(t.unitPrice) / SUM(t.quantityIn - t.quantityOut)), 0) AS stock FROM AppBundle\Entity\Report\Inventory t WHERE t.product = :product');
        $query->setParameter('product', $product);
        $stock = $query->getSingleScalarResult();
        
        return $stock;
    }
}
