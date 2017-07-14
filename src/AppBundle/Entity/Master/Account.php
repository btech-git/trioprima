<?php

namespace AppBundle\Entity\Master;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="master_account") @ORM\Entity
 * @UniqueEntity("code") @UniqueEntity("name")
 */
class Account
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
     * @Assert\NotBlank()
     */
    private $name;
    /**
     * @ORM\Column(name="is_active", type="boolean")
     * @Assert\NotNull()
     */
    private $isActive = true;
    /**
     * @ORM\ManyToOne(targetEntity="AccountCategory", inversedBy="accounts")
     * @Assert\NotNull()
     */
    private $accountCategory;
    /**
     * @ORM\OneToMany(targetEntity="Customer", mappedBy="account")
     */
    private $customers;
    /**
     * @ORM\OneToMany(targetEntity="Supplier", mappedBy="account")
     */
    private $suppliers;
    
    public function __construct()
    {
        $this->customers = new ArrayCollection();
        $this->suppliers = new ArrayCollection();
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
    
    public function getIsActive() { return $this->isActive; }
    public function setIsActive($isActive) { $this->isActive = $isActive; }
    
    public function getAccountCategory() { return $this->accountCategory; }
    public function setAccountCategory(AccountCategory $accountCategory = null) { $this->accountCategory = $accountCategory; }
    
    public function getCustomers() { return $this->customers; }
    public function setCustomers(Collection $customers) { $this->customers = $customers; }
    
    public function getSuppliers() { return $this->suppliers; }
    public function setSuppliers(Collection $suppliers) { $this->suppliers = $suppliers; }
}
