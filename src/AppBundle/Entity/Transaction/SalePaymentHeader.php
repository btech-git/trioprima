<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Common\CodeNumberEntity;
use AppBundle\Entity\Master\Customer;
use AppBundle\Entity\Master\PaymentType;
use AppBundle\Entity\Admin\Staff;

/**
 * @ORM\Table(name="transaction_sale_payment_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Transaction\SalePaymentHeaderRepository")
 */
class SalePaymentHeader extends CodeNumberEntity
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Master\Customer")
     * @Assert\NotNull()
     */
    private $customer;
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
     * @ORM\OneToMany(targetEntity="SalePaymentDetail", mappedBy="salePaymentHeader")
     * @Assert\Valid() @Assert\Count(min=1)
     */
    private $salePaymentDetails;
    
    public function __construct()
    {
        $this->salePaymentDetails = new ArrayCollection();
    }
    
    public function getCodeNumberConstant() { return 'SPY'; }
    
    public function getId() { return $this->id; }
    
    public function getTransactionDate() { return $this->transactionDate; }
    public function setTransactionDate(\DateTime $transactionDate = null) { $this->transactionDate = $transactionDate; }
    
    public function getNote() { return $this->note; }
    public function setNote($note) { $this->note = $note; }
    
    public function getCustomer() { return $this->customer; }
    public function setCustomer(Customer $customer = null) { $this->customer = $customer; }
    
    public function getPaymentType() { return $this->paymentType; }
    public function setPaymentType(PaymentType $paymentType = null) { $this->paymentType = $paymentType; }
    
    public function getStaff() { return $this->staff; }
    public function setStaff(Staff $staff = null) { $this->staff = $staff; }
    
    public function getSalePaymentDetails() { return $this->salePaymentDetails; }
    public function setSalePaymentDetails(Collection $salePaymentDetails) { $this->salePaymentDetails = $salePaymentDetails; }
    
    public function getTotalAmount()
    {
        $total = 0.00;
        foreach ($this->salePaymentDetails as $salePaymentDetail) {
            $total += $salePaymentDetail->getAmount();
        }
        
        return $total;
    }
}
