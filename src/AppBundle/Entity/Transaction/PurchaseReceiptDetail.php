<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="transaction_purchase_receipt_detail") @ORM\Entity
 */
class PurchaseReceiptDetail
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
     * @ORM\ManyToOne(targetEntity="PurchaseInvoiceHeader", inversedBy="purchaseReceiptDetails")
     * @Assert\NotNull()
     */
    private $purchaseInvoiceHeader;
    /**
     * @ORM\ManyToOne(targetEntity="PurchaseReceiptHeader", inversedBy="purchaseReceiptDetails")
     * @Assert\NotNull()
     */
    private $purchaseReceiptHeader;
    
    public function getId() { return $this->id; }
    
    public function getMemo() { return $this->memo; }
    public function setMemo($memo) { $this->memo = $memo; }
    
    public function getPurchaseInvoiceHeader() { return $this->purchaseInvoiceHeader; }
    public function setPurchaseInvoiceHeader(PurchaseInvoiceHeader $purchaseInvoiceHeader = null) { $this->purchaseInvoiceHeader = $purchaseInvoiceHeader; }
    
    public function getPurchaseReceiptHeader() { return $this->purchaseReceiptHeader; }
    public function setPurchaseReceiptHeader(PurchaseReceiptHeader $purchaseReceiptHeader = null) { $this->purchaseReceiptHeader = $purchaseReceiptHeader; }
}
