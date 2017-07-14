<?php

namespace AppBundle\Entity\Master;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="master_product") @ORM\Entity
 * @UniqueEntity("code")
 */
class Product
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
     * @ORM\Column(name="name", type="string", length=100)
     * @Assert\NotBlank()
     */
    private $name;
    /**
     * @ORM\Column(name="size", type="string", length=20)
     * @Assert\NotNull()
     */
    private $size;
    /**
     * @ORM\Column(name="physical_code", type="string", length=60)
     * @Assert\NotNull()
     */
    private $physicalCode;
    /**
     * @ORM\Column(name="minimum_stock", type="smallint")
     * @Assert\NotNull()
     */
    private $minimumStock;
    /**
     * @ORM\Column(name="is_active", type="boolean")
     * @Assert\NotNull()
     */
    private $isActive = true;
    /**
     * @ORM\ManyToOne(targetEntity="ProductCategory", inversedBy="products")
     * @Assert\NotNull()
     */
    private $productCategory;
    /**
     * @ORM\ManyToOne(targetEntity="Unit", inversedBy="products")
     * @Assert\NotNull()
     */
    private $unit;
    /**
     * @ORM\ManyToOne(targetEntity="Brand", inversedBy="products")
     * @Assert\NotNull()
     */
    private $brand;
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Transaction\PurchaseInvoiceDetail", mappedBy="product")
     */
    private $purchaseInvoiceDetails;
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Transaction\SaleInvoiceDetail", mappedBy="product")
     */
    private $saleInvoiceDetails;
    
    public function __construct()
    {
        $this->purchaseInvoiceDetails = new ArrayCollection();
        $this->saleInvoiceDetails = new ArrayCollection();
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
    
    public function getSize() { return $this->size; }
    public function setSize($size) { $this->size = $size; }
    
    public function getPhysicalCode() { return $this->physicalCode; }
    public function setPhysicalCode($physicalCode) { $this->physicalCode = $physicalCode; }
    
    public function getMinimumStock() { return $this->minimumStock; }
    public function setMinimumStock($minimumStock) { $this->minimumStock = $minimumStock; }
    
    public function getIsActive() { return $this->isActive; }
    public function setIsActive($isActive) { $this->isActive = $isActive; }
    
    public function getProductCategory() { return $this->productCategory; }
    public function setProductCategory(ProductCategory $productCategory = null) { $this->productCategory = $productCategory; }
    
    public function getUnit() { return $this->unit; }
    public function setUnit(Unit $unit = null) { $this->unit = $unit; }
    
    public function getBrand() { return $this->brand; }
    public function setBrand(Brand $brand = null) { $this->brand = $brand; }
    
    public function getPurchaseInvoiceDetails() { return $this->purchaseInvoiceDetails; }
    public function setPurchaseInvoiceDetails(Collection $purchaseInvoiceDetails) { $this->purchaseInvoiceDetails = $purchaseInvoiceDetails; }
    
    public function getSaleInvoiceDetails() { return $this->saleInvoiceDetails; }
    public function setSaleInvoiceDetails(Collection $saleInvoiceDetails) { $this->saleInvoiceDetails = $saleInvoiceDetails; }
}
