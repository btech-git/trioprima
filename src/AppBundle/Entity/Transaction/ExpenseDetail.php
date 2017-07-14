<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Master\Account;

/**
 * @ORM\Table(name="transaction_expense_detail") @ORM\Entity
 */
class ExpenseDetail
{
    /**
     * @ORM\Column(name="id", type="integer") @ORM\Id @ORM\GeneratedValue
     */
    private $id;
    /**
     * @ORM\Column(name="description", type="string", length=100)
     * @Assert\NotNull()
     */
    private $description;
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
     * @ORM\ManyToOne(targetEntity="ExpenseHeader", inversedBy="expenseDetails")
     * @Assert\NotNull()
     */
    private $expenseHeader;
    
    public function getId() { return $this->id; }
    
    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }
    
    public function getAmount() { return $this->amount; }
    public function setAmount($amount) { $this->amount = $amount; }
    
    public function getMemo() { return $this->memo; }
    public function setMemo($memo) { $this->memo = $memo; }
    
    public function getAccount() { return $this->account; }
    public function setAccount(Account $account = null) { $this->account = $account; }
    
    public function getExpenseHeader() { return $this->expenseHeader; }
    public function setExpenseHeader(ExpenseHeader $expenseHeader = null) { $this->expenseHeader = $expenseHeader; }
}
