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
}
