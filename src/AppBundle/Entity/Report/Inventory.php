<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Common\CodeNumberEntity;
use AppBundle\Entity\Master\Product;
use AppBundle\Entity\Admin\Staff;

/**
 * @ORM\Table(name="report_inventory")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Report\InventoryRepository")
 */
class Inventory extends CodeNumberEntity
{
    const TRANSACTION_TYPE_RECEIVE = 'receive';
    const TRANSACTION_TYPE_DELIVERY = 'delivery';
    const TRANSACTION_TYPE_PURCHASE_RETURN = 'purchase_return';
    const TRANSACTION_TYPE_SALE_RETURN = 'sale_return';
    const TRANSACTION_TYPE_ADJUSTMENT = 'adjustment';
    
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
     * @ORM\Column(name="transaction_type", type="string", length=20)
     * @Assert\NotBlank()
     */
    private $transactionType;
    /**
     * @ORM\Column(name="transaction_subject", type="string", length=60)
     * @Assert\NotBlank()
     */
    private $transactionSubject;
    /**
     * @ORM\Column(name="note", type="text")
     * @Assert\NotNull()
     */
    private $note;
    /**
     * @ORM\Column(name="quantity_in", type="smallint")
     * @Assert\NotNull()
     */
    private $quantityIn;
    /**
     * @ORM\Column(name="quantity_out", type="smallint")
     * @Assert\NotNull()
     */
    private $quantityOut;
    /**
     * @ORM\Column(name="unit_price", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThan(0)
     */
    private $unitPrice;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Master\Product")
     * @Assert\NotNull()
     */
    private $product;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Admin\Staff")
     * @Assert\NotNull()
     */
    private $staff;
    
    public function getCodeNumberConstant()
    {
        
    }
    
    public function getId() { return $this->id; }
    
    public function getTransactionOrdinal() { return $this->transactionOrdinal; }
    public function setTransactionOrdinal($transactionOrdinal) { $this->transactionOrdinal = $transactionOrdinal; }
    
    public function getTransactionMonth() { return $this->transactionMonth; }
    public function setTransactionMonth($transactionMonth) { $this->transactionMonth = $transactionMonth; }
    
    public function getTransactionYear() { return $this->transactionYear; }
    public function setTransactionYear($transactionYear) { $this->transactionYear = $transactionYear; }
    
    public function getTransactionDate() { return $this->transactionDate; }
    public function setTransactionDate(\DateTime $transactionDate = null) { $this->transactionDate = $transactionDate; }
    
    public function getTransactionType() { return $this->transactionType; }
    public function setTransactionType($transactionType) { $this->transactionType = $transactionType; }
    
    public function getTransactionSubject() { return $this->transactionSubject; }
    public function setTransactionSubject($transactionSubject) { $this->transactionSubject = $transactionSubject; }
    
    public function getNote() { return $this->note; }
    public function setNote($note) { $this->note = $note; }
    
    public function getQuantityIn() { return $this->quantityIn; }
    public function setQuantityIn($quantityIn) { $this->quantityIn = $quantityIn; }
    
    public function getQuantityOut() { return $this->quantityOut; }
    public function setQuantityOut($quantityOut) { $this->quantityOut = $quantityOut; }
    
    public function getUnitPrice() { return $this->unitPrice; }
    public function setUnitPrice($unitPrice) { $this->unitPrice = $unitPrice; }
    
    public function getProduct() { return $this->product; }
    public function setProduct(Product $product = null) { $this->product = $product; }
    
    public function getStaff() { return $this->staff; }
    public function setStaff(Staff $staff = null) { $this->staff = $staff; }
}
