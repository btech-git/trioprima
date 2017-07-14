<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Master\Account;

/**
 * @ORM\Table(name="transaction_sale_payment_detail") @ORM\Entity
 */
class SalePaymentDetail
{
    /**
     * @ORM\Column(name="id", type="integer") @ORM\Id @ORM\GeneratedValue
     */
    private $id;
    /**
     * @ORM\Column(name="amount", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThan(0)
     */
    private $amount;
    /**
     * @ORM\Column(name="memo", type="string", length=100)
     * @Assert\NotNull()
     */
    private $memo;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Master\Account")
     * @Assert\NotNull()
     */
    private $account;
    /**
     * @ORM\ManyToOne(targetEntity="SaleInvoiceHeader", inversedBy="salePaymentDetails")
     * @Assert\NotNull()
     */
    private $saleInvoiceHeader;
    /**
     * @ORM\ManyToOne(targetEntity="SalePaymentHeader", inversedBy="salePaymentDetails")
     * @Assert\NotNull()
     */
    private $salePaymentHeader;
    
    public function getId() { return $this->id; }
    
    public function getAmount() { return $this->amount; }
    public function setAmount($amount) { $this->amount = $amount; }
    
    public function getMemo() { return $this->memo; }
    public function setMemo($memo) { $this->memo = $memo; }
    
    public function getAccount() { return $this->account; }
    public function setAccount(Account $account = null) { $this->account = $account; }
    
    public function getSaleInvoiceHeader() { return $this->saleInvoiceHeader; }
    public function setSaleInvoiceHeader(SaleInvoiceHeader $saleInvoiceHeader = null) { $this->saleInvoiceHeader = $saleInvoiceHeader; }
    
    public function getSalePaymentHeader() { return $this->salePaymentHeader; }
    public function setSalePaymentHeader(SalePaymentHeader $salePaymentHeader = null) { $this->salePaymentHeader = $salePaymentHeader; }
}
