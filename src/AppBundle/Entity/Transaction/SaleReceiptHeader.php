<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Common\CodeNumberEntity;
use AppBundle\Entity\Master\Customer;
use AppBundle\Entity\Admin\Staff;

/**
 * @ORM\Table(name="transaction_sale_receipt_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Transaction\SaleReceiptHeaderRepository")
 */
class SaleReceiptHeader extends CodeNumberEntity
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Admin\Staff")
     * @Assert\NotNull()
     */
    private $staff;
    /**
     * @ORM\OneToMany(targetEntity="SaleReceiptDetail", mappedBy="saleReceiptHeader")
     * @Assert\Valid() @Assert\Count(min=1)
     */
    private $saleReceiptDetails;
    
    public function __construct()
    {
        $this->saleReceiptDetails = new ArrayCollection();
    }
    
    public function getCodeNumberConstant() { return 'SRC'; }
    
    public function getId() { return $this->id; }
    
    public function getTransactionDate() { return $this->transactionDate; }
    public function setTransactionDate(\DateTime $transactionDate = null) { $this->transactionDate = $transactionDate; }
    
    public function getNote() { return $this->note; }
    public function setNote($note) { $this->note = $note; }
    
    public function getCustomer() { return $this->customer; }
    public function setCustomer(Customer $customer = null) { $this->customer = $customer; }
    
    public function getStaff() { return $this->staff; }
    public function setStaff(Staff $staff = null) { $this->staff = $staff; }
    
    public function getSaleReceiptDetails() { return $this->saleReceiptDetails; }
    public function setSaleReceiptDetails(Collection $saleReceiptDetails) { $this->saleReceiptDetails = $saleReceiptDetails; }
}
