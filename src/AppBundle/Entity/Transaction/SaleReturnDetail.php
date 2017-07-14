<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="transaction_sale_return_detail") @ORM\Entity
 */
class SaleReturnDetail
{
    /**
     * @ORM\Column(name="id", type="integer") @ORM\Id @ORM\GeneratedValue
     */
    private $id;
    /**
     * @ORM\Column(name="quantity", type="smallint")
     * @Assert\NotNull() @Assert\GreaterThan(0)
     */
    private $quantity;
    /**
     * @ORM\ManyToOne(targetEntity="SaleInvoiceDetail", inversedBy="saleReturnDetails")
     * @Assert\NotNull()
     */
    private $saleInvoiceDetail;
    /**
     * @ORM\ManyToOne(targetEntity="SaleReturnHeader", inversedBy="saleReturnDetails")
     * @Assert\NotNull()
     */
    private $saleReturnHeader;
    
    public function getId() { return $this->id; }
    
    public function getQuantity() { return $this->quantity; }
    public function setQuantity($quantity) { $this->quantity = $quantity; }
    
    public function getSaleInvoiceDetail() { return $this->saleInvoiceDetail; }
    public function setSaleInvoiceDetail(SaleInvoiceDetail $saleInvoiceDetail = null) { $this->saleInvoiceDetail = $saleInvoiceDetail; }
    
    public function getSaleReturnHeader() { return $this->saleReturnHeader; }
    public function setSaleReturnHeader(SaleReturnHeader $saleReturnHeader = null) { $this->saleReturnHeader = $saleReturnHeader; }
    
    public function getTotal()
    {
        $saleInvoiceDetail = $this->getSaleInvoiceDetail();
        $unitPrice = $saleInvoiceDetail === null ? 0.00 : $saleInvoiceDetail->getUnitPrice();
        
        return $this->quantity * $unitPrice;
    }
}
