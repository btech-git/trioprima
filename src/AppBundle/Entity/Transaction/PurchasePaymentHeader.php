<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Common\CodeNumberEntity;
use AppBundle\Entity\Master\Supplier;
use AppBundle\Entity\Master\PaymentType;
use AppBundle\Entity\Admin\Staff;

/**
 * @ORM\Table(name="transaction_purchase_payment_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Transaction\PurchasePaymentHeaderRepository")
 */
class PurchasePaymentHeader extends CodeNumberEntity
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
     * @ORM\Column(name="note", type="text")
     * @Assert\NotNull()
     */
    private $note;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Master\Supplier")
     * @Assert\NotNull()
     */
    private $supplier;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Master\PaymentType")
     * @Assert\NotNull()
     */
    private $paymentType;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Admin\Staff")
     * @Assert\NotNull()
     */
    private $staff;
    /**
     * @ORM\OneToMany(targetEntity="PurchasePaymentDetail", mappedBy="purchasePaymentHeader")
     * @Assert\Valid() @Assert\Count(min=1)
     */
    private $purchasePaymentDetails;
    
    public function __construct()
    {
        $this->purchasePaymentDetails = new ArrayCollection();
    }
    
    public function getCodeNumberConstant() { return 'PPY'; }
    
    public function getId() { return $this->id; }
    
    public function getTransactionDate() { return $this->transactionDate; }
    public function setTransactionDate(\DateTime $transactionDate = null) { $this->transactionDate = $transactionDate; }
    
    public function getNote() { return $this->note; }
    public function setNote($note) { $this->note = $note; }
    
    public function getSupplier() { return $this->supplier; }
    public function setSupplier(Supplier $supplier = null) { $this->supplier = $supplier; }
    
    public function getPaymentType() { return $this->paymentType; }
    public function setPaymentType(PaymentType $paymentType = null) { $this->paymentType = $paymentType; }
    
    public function getStaff() { return $this->staff; }
    public function setStaff(Staff $staff = null) { $this->staff = $staff; }
    
    public function getPurchasePaymentDetails() { return $this->purchasePaymentDetails; }
    public function setPurchasePaymentDetails(Collection $purchasePaymentDetails) { $this->purchasePaymentDetails = $purchasePaymentDetails; }
    
    public function getTotalAmount()
    {
        $total = 0.00;
        foreach ($this->purchasePaymentDetails as $purchasePaymentDetail) {
            $total += $purchasePaymentDetail->getAmount();
        }
        
        return $total;
    }
}
