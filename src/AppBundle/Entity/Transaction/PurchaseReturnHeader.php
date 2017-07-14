<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Common\CodeNumberEntity;
use AppBundle\Entity\Admin\Staff;

/**
 * @ORM\Table(name="transaction_purchase_return_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Transaction\PurchaseReturnHeaderRepository")
 */
class PurchaseReturnHeader extends CodeNumberEntity
{
    /**
     * @ORM\Column(name="id", type="integer") @ORM\Id @ORM\GeneratedValue
     */
    private $id;
    /**
     * @ORM\Column(name="transaction_date", type="date")
     * @Assert\NotNull() @Assert\Date()
     */
    private $transactionDate;
    /**
     * @ORM\Column(name="shipping_fee", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThanOrEqual(0)
     */
    private $shippingFee;
    /**
     * @ORM\Column(name="note", type="text")
     * @Assert\NotNull()
     */
    private $note;
    /**
     * @ORM\Column(name="is_tax", type="boolean")
     * @Assert\NotNull()
     */
    private $isTax;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Admin\Staff")
     * @Assert\NotNull()
     */
    private $staff;
    /**
     * @ORM\ManyToOne(targetEntity="PurchaseInvoiceHeader", inversedBy="purchaseReturnHeaders")
     * @Assert\NotNull()
     */
    private $purchaseInvoiceHeader;
    /**
     * @ORM\OneToMany(targetEntity="PurchaseReturnDetail", mappedBy="purchaseReturnHeader")
     * @Assert\Valid() @Assert\Count(min=1)
     */
    private $purchaseReturnDetails;
    
    public function __construct()
    {
        $this->purchaseReturnDetails = new ArrayCollection();
    }
    
    public function getCodeNumberConstant() { return 'PRT'; }
    
    public function getId() { return $this->id; }
    
    public function getTransactionDate() { return $this->transactionDate; }
    public function setTransactionDate(\DateTime $transactionDate = null) { $this->transactionDate = $transactionDate; }
    
    public function getShippingFee() { return $this->shippingFee; }
    public function setShippingFee($shippingFee) { $this->shippingFee = $shippingFee; }
    
    public function getNote() { return $this->note; }
    public function setNote($note) { $this->note = $note; }
    
    public function getIsTax() { return $this->isTax; }
    public function setIsTax($isTax) { $this->isTax = $isTax; }
    
    public function getStaff() { return $this->staff; }
    public function setStaff(Staff $staff = null) { $this->staff = $staff; }
    
    public function getPurchaseInvoiceHeader() { return $this->purchaseInvoiceHeader; }
    public function setPurchaseInvoiceHeader(PurchaseInvoiceHeader $purchaseInvoiceHeader = null) { $this->purchaseInvoiceHeader = $purchaseInvoiceHeader; }
    
    public function getPurchaseReturnDetails() { return $this->purchaseReturnDetails; }
    public function setPurchaseReturnDetails(Collection $purchaseReturnDetails) { $this->purchaseReturnDetails = $purchaseReturnDetails; }
    
    public function getTaxPercentage()
    {
        return $this->isTax ? 10 : 0;
    }
    
    public function getTaxNominal()
    {
        return $this->getSubTotal() * $this->getTaxPercentage() / 100;
    }
    
    public function getSubTotal()
    {
        $total = 0.00;
        foreach ($this->purchaseReturnDetails as $purchaseReturnDetail) {
            $total += $purchaseReturnDetail->getTotal();
        }
        
        return $total;
    }
    
    public function getGrandTotal()
    {
        return $this->getSubTotal() + $this->getTaxNominal() + $this->shippingFee;
    }
}
