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
 * @ORM\Table(name="transaction_deposit_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Transaction\DepositHeaderRepository")
 */
class DepositHeader extends CodeNumberEntity
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
     * @ORM\OneToMany(targetEntity="DepositDetail", mappedBy="depositHeader")
     * @Assert\Valid() @Assert\Count(min=1)
     */
    private $depositDetails;
    
    public function __construct()
    {
        $this->depositDetails = new ArrayCollection();
    }
    
    public function getCodeNumberConstant() { return 'DPS'; }
    
    public function getId() { return $this->id; }
    
    public function getTransactionDate() { return $this->transactionDate; }
    public function setTransactionDate(\DateTime $transactionDate = null) { $this->transactionDate = $transactionDate; }
    
    public function getNote() { return $this->note; }
    public function setNote($note) { $this->note = $note; }
    
    public function getAccount() { return $this->account; }
    public function setAccount(Account $account = null) { $this->account = $account; }
    
    public function getStaff() { return $this->staff; }
    public function setStaff(Staff $staff = null) { $this->staff = $staff; }
    
    public function getDepositDetails() { return $this->depositDetails; }
    public function setDepositDetails(Collection $depositDetails) { $this->depositDetails = $depositDetails; }
    
    public function getTotalAmount()
    {
        $total = 0.00;
        foreach ($this->depositDetails as $depositDetail) {
            $total += $depositDetail->getAmount();
        }
        
        return $total;
    }
}
