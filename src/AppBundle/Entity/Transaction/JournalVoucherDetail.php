<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Master\Account;

/**
 * @ORM\Table(name="transaction_journal_voucher_detail") @ORM\Entity
 * @Assert\Expression("(this.getDebit() == 0 and this.getCredit() > 0) or (this.getDebit() > 0 and this.getCredit() == 0)")
 */
class JournalVoucherDetail
{
    /**
     * @ORM\Column(name="id", type="integer") @ORM\Id @ORM\GeneratedValue
     */
    private $id;
    /**
     * @ORM\Column(name="debit", type="decimal", precision=18, scale=2)
     * @Assert\NotNull()
     */
    private $debit;
    /**
     * @ORM\Column(name="credit", type="decimal", precision=18, scale=2)
     * @Assert\NotNull()
     */
    private $credit;
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
     * @ORM\ManyToOne(targetEntity="JournalVoucherHeader", inversedBy="journalVoucherDetails")
     * @Assert\NotNull()
     */
    private $journalVoucherHeader;
    
    public function getId() { return $this->id; }
    
    public function getDebit() { return $this->debit; }
    public function setDebit($debit) { $this->debit = $debit; }
    
    public function getCredit() { return $this->credit; }
    public function setCredit($credit) { $this->credit = $credit; }
    
    public function getMemo() { return $this->memo; }
    public function setMemo($memo) { $this->memo = $memo; }
    
    public function getAccount() { return $this->account; }
    public function setAccount(Account $account = null) { $this->account = $account; }
    
    public function getJournalVoucherHeader() { return $this->journalVoucherHeader; }
    public function setJournalVoucherHeader(JournalVoucherHeader $journalVoucherHeader = null) { $this->journalVoucherHeader = $journalVoucherHeader; }
}
