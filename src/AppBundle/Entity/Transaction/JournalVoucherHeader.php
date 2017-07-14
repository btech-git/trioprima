<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Common\CodeNumberEntity;
use AppBundle\Entity\Admin\Staff;

/**
 * @ORM\Table(name="transaction_journal_voucher_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Transaction\JournalVoucherHeaderRepository")
 */
class JournalVoucherHeader extends CodeNumberEntity
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Admin\Staff")
     * @Assert\NotNull()
     */
    private $staff;
    /**
     * @ORM\OneToMany(targetEntity="JournalVoucherDetail", mappedBy="journalVoucherHeader")
     * @Assert\Valid() @Assert\Count(min=1)
     */
    private $journalVoucherDetails;
    
    public function __construct()
    {
        $this->journalVoucherDetails = new ArrayCollection();
    }
    
    public function getCodeNumberConstant() { return 'JVC'; }
    
    public function getId() { return $this->id; }
    
    public function getTransactionDate() { return $this->transactionDate; }
    public function setTransactionDate(\DateTime $transactionDate = null) { $this->transactionDate = $transactionDate; }
    
    public function getNote() { return $this->note; }
    public function setNote($note) { $this->note = $note; }
    
    public function getStaff() { return $this->staff; }
    public function setStaff(Staff $staff = null) { $this->staff = $staff; }
    
    public function getJournalVoucherDetails() { return $this->journalVoucherDetails; }
    public function setJournalVoucherDetails(Collection $journalVoucherDetails) { $this->journalVoucherDetails = $journalVoucherDetails; }
    
    public function getTotalDebit()
    {
        $total = 0.00;
        foreach ($this->journalVoucherDetails as $journalVoucherDetail) {
            $total += $journalVoucherDetail->getDebit();
        }
        
        return $total;
    }
    
    public function getTotalCredit()
    {
        $total = 0.00;
        foreach ($this->journalVoucherDetails as $journalVoucherDetail) {
            $total += $journalVoucherDetail->getCredit();
        }
        
        return $total;
    }
}
