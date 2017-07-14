<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="transaction_sale_receipt_detail") @ORM\Entity
 */
class SaleReceiptDetail
{
    /**
     * @ORM\Column(name="id", type="integer") @ORM\Id @ORM\GeneratedValue
     */
    private $id;
    /**
     * @ORM\Column(name="memo", type="string", length=100)
     * @Assert\NotNull()
     */
    private $memo;
    /**
     * @ORM\ManyToOne(targetEntity="SaleInvoiceHeader", inversedBy="saleReceiptDetails")
     * @Assert\NotNull()
     */
    private $saleInvoiceHeader;
    /**
     * @ORM\ManyToOne(targetEntity="SaleReceiptHeader", inversedBy="saleReceiptDetails")
     * @Assert\NotNull()
     */
    private $saleReceiptHeader;
    
    public function getId() { return $this->id; }
    
    public function getMemo() { return $this->memo; }
    public function setMemo($memo) { $this->memo = $memo; }
    
    public function getSaleInvoiceHeader() { return $this->saleInvoiceHeader; }
    public function setSaleInvoiceHeader(SaleInvoiceHeader $saleInvoiceHeader = null) { $this->saleInvoiceHeader = $saleInvoiceHeader; }
    
    public function getSaleReceiptHeader() { return $this->saleReceiptHeader; }
    public function setSaleReceiptHeader(SaleReceiptHeader $saleReceiptHeader = null) { $this->saleReceiptHeader = $saleReceiptHeader; }
}
