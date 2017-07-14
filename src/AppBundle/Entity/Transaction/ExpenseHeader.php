<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Common\CodeNumberEntity;
use AppBundle\Entity\Master\Account;
use AppBundle\Entity\Admin\Staff;

/**
 * @ORM\Table(name="transaction_expense_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Transaction\ExpenseHeaderRepository")
 */
class ExpenseHeader extends CodeNumberEntity
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Master\Account")
     * @Assert\NotNull()
     */
    private $account;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Admin\Staff")
     * @Assert\NotNull()
     */
    private $staff;
    /**
     * @ORM\OneToMany(targetEntity="ExpenseDetail", mappedBy="expenseHeader")
     * @Assert\Valid() @Assert\Count(min=1)
     */
    private $expenseDetails;
    
    public function __construct()
    {
        $this->expenseDetails = new ArrayCollection();
    }
    
    public function getCodeNumberConstant() { return 'EXP'; }
    
    public function getId() { return $this->id; }
    
    public function getTransactionDate() { return $this->transactionDate; }
    public function setTransactionDate(\DateTime $transactionDate = null) { $this->transactionDate = $transactionDate; }
    
    public function getNote() { return $this->note; }
    public function setNote($note) { $this->note = $note; }
    
    public function getAccount() { return $this->account; }
    public function setAccount(Account $account = null) { $this->account = $account; }
    
    public function getStaff() { return $this->staff; }
    public function setStaff(Staff $staff = null) { $this->staff = $staff; }
    
    public function getExpenseDetails() { return $this->expenseDetails; }
    public function setExpenseDetails(Collection $expenseDetails) { $this->expenseDetails = $expenseDetails; }
    
    public function getTotalAmount()
    {
        $total = 0.00;
        foreach ($this->expenseDetails as $expenseDetail) {
            $total += $expenseDetail->getAmount();
        }
        
        return $total;
    }
}
