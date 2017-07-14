<?php

namespace AppBundle\Entity\Master;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="master_customer") @ORM\Entity
 * @UniqueEntity("code")
 */
class Customer
{
    /**
     * @ORM\Column(name="id", type="integer") @ORM\Id @ORM\GeneratedValue
     */
    private $id;
    /**
     * @ORM\Column(name="code", type="string", length=20, unique=true)
     * @Assert\NotBlank()
     */
    private $code;
    /**
     * @ORM\Column(name="name", type="string", length=60)
     * @Assert\NotNull()
     */
    private $name;
    /**
     * @ORM\Column(name="company", type="string", length=60)
     * @Assert\NotBlank()
     */
    private $company;
    /**
     * @ORM\Column(name="address", type="text")
     * @Assert\NotNull()
     */
    private $address;
    /**
     * @ORM\Column(name="phone", type="string", length=20)
     * @Assert\NotNull()
     */
    private $phone;
    /**
     * @ORM\Column(name="fax", type="string", length=20)
     * @Assert\NotNull()
     */
    private $fax;
    /**
     * @ORM\Column(name="email", type="string", length=60)
     * @Assert\NotNull() @Assert\Email()
     */
    private $email;
    /**
     * @ORM\Column(name="tax_number", type="string", length=20)
     * @Assert\NotNull()
     */
    private $taxNumber;
    /**
     * @ORM\Column(name="note", type="text")
     * @Assert\NotNull()
     */
    private $note;
    /**
     * @ORM\Column(name="is_active", type="boolean")
     * @Assert\NotNull()
     */
    private $isActive = true;
    /**
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="customers")
     * @Assert\NotNull()
     */
    private $account;
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Transaction\SaleInvoiceHeader", mappedBy="customer")
     */
    private $saleInvoiceHeaders;
    
    public function __construct()
    {
        $this->saleInvoiceHeaders = new ArrayCollection();
    }
    
    public function __toString()
    {
        return $this->name;
    }
    
    public function getId() { return $this->id; }
    
    public function getCode() { return $this->code; }
    public function setCode($code) { $this->code = $code; }
    
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
    
    public function getCompany() { return $this->company; }
    public function setCompany($company) { $this->company = $company; }
    
    public function getAddress() { return $this->address; }
    public function setAddress($address) { $this->address = $address; }
    
    public function getPhone() { return $this->phone; }
    public function setPhone($phone) { $this->phone = $phone; }
    
    public function getFax() { return $this->fax; }
    public function setFax($fax) { $this->fax = $fax; }
    
    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }
    
    public function getTaxNumber() { return $this->taxNumber; }
    public function setTaxNumber($taxNumber) { $this->taxNumber = $taxNumber; }
    
    public function getNote() { return $this->note; }
    public function setNote($note) { $this->note = $note; }
    
    public function getIsActive() { return $this->isActive; }
    public function setIsActive($isActive) { $this->isActive = $isActive; }
    
    public function getAccount() { return $this->account; }
    public function setAccount(Account $account = null) { $this->account = $account; }
    
    public function getSaleInvoiceHeaders() { return $this->saleInvoiceHeaders; }
    public function setSaleInvoiceHeaders(Collection $saleInvoiceHeaders = null) { $this->saleInvoiceHeaders = $saleInvoiceHeaders; }
}
