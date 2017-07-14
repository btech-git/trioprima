<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Master\Product;

/**
 * @ORM\Table(name="transaction_purchase_invoice_detail")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Transaction\PurchaseInvoiceDetailRepository")
 */
class PurchaseInvoiceDetail
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
     * @ORM\Column(name="unit_price", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThan(0)
     */
    private $unitPrice;
    /**
     * @ORM\Column(name="discount", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThanOrEqual(0)
     */
    private $discount;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Master\Product", inversedBy="purchaseInvoiceDetails")
     * @Assert\NotNull()
     */
    private $product;
    /**
     * @ORM\ManyToOne(targetEntity="PurchaseInvoiceHeader", inversedBy="purchaseInvoiceDetails")
     * @Assert\NotNull()
     */
    private $purchaseInvoiceHeader;
    /**
     * @ORM\OneToMany(targetEntity="PurchaseReturnDetail", mappedBy="purchaseInvoiceDetail")
     */
    private $purchaseReturnDetails;
    
    public function __construct()
    {
        $this->purchaseReturnDetails = new ArrayCollection();
    }
    
    public function getId() { return $this->id; }
    
    public function getQuantity() { return $this->quantity; }
    public function setQuantity($quantity) { $this->quantity = $quantity; }
    
    public function getUnitPrice() { return $this->unitPrice; }
    public function setUnitPrice($unitPrice) { $this->unitPrice = $unitPrice; }
    
    public function getDiscount() { return $this->discount; }
    public function setDiscount($discount) { $this->discount = $discount; }
    
    public function getProduct() { return $this->product; }
    public function setProduct(Product $product = null) { $this->product = $product; }
    
    public function getPurchaseInvoiceHeader() { return $this->purchaseInvoiceHeader; }
    public function setPurchaseInvoiceHeader(PurchaseInvoiceHeader $purchaseInvoiceHeader = null) { $this->purchaseInvoiceHeader = $purchaseInvoiceHeader; }
    
    public function getPurchaseReturnDetails() { return $this->purchaseReturnDetails; }
    public function setPurchaseReturnDetails(Collection $purchaseReturnDetails) { $this->purchaseReturnDetails = $purchaseReturnDetails; }
    
    public function getTotal()
    {
        return $this->quantity * $this->unitPrice * (1 - $this->discount / 100);
    }
}
