<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Common\CodeNumberEntity;
use AppBundle\Entity\Master\Supplier;
use AppBundle\Entity\Admin\Staff;

/**
 * @ORM\Table(name="transaction_purchase_invoice_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Transaction\PurchaseInvoiceHeaderRepository")
 */
class PurchaseInvoiceHeader extends CodeNumberEntity
{
    const DISCOUNT_TYPE_PERCENTAGE = 'percentage';
    const DISCOUNT_TYPE_NOMINAL = 'nominal';
    
    const DOWNPAYMENT_TYPE_PERCENTAGE = 'percentage';
    const DOWNPAYMENT_TYPE_NOMINAL = 'nominal';
    
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
     * @ORM\Column(name="date_approved", type="date", nullable=true)
     * @Assert\Date()
     */
    private $dateApproved;
    /**
     * @ORM\Column(name="tax_invoice_code", type="string", length=60)
     * @Assert\NotNull()
     */
    private $taxInvoiceCode;
    /**
     * @ORM\Column(name="supplier_invoice", type="string", length=60)
     * @Assert\NotNull()
     */
    private $supplierInvoice;
    /**
     * @ORM\Column(name="downpayment_type", type="string", length=20)
     * @Assert\NotNull()
     */
    private $downpaymentType;
    /**
     * @ORM\Column(name="downpayment_value", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThanOrEqual(0)
     */
    private $downpaymentValue;
    /**
     * @ORM\Column(name="discount_type", type="string", length=20)
     * @Assert\NotNull()
     */
    private $discountType;
    /**
     * @ORM\Column(name="discount_value", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThanOrEqual(0)
     */
    private $discountValue;
    /**
     * @ORM\Column(name="tax_nominal", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThanOrEqual(0)
     */
    private $taxNominal;
    /**
     * @ORM\Column(name="shipping_fee", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThanOrEqual(0)
     */
    private $shippingFee;
    /**
     * @ORM\Column(name="sub_total", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThan(0)
     */
    private $subTotal;
    /**
     * @ORM\Column(name="grand_total_before_downpayment", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThan(0)
     */
    private $grandTotalBeforeDownpayment;
    /**
     * @ORM\Column(name="grand_total_after_downpayment", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThan(0)
     */
    private $grandTotalAfterDownpayment;
    /**
     * @ORM\Column(name="total_payment", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThanOrEqual(0)
     */
    private $totalPayment;
    /**
     * @ORM\Column(name="total_return", type="decimal", precision=18, scale=2)
     * @Assert\NotNull() @Assert\GreaterThanOrEqual(0)
     */
    private $totalReturn;
    /**
     * @ORM\Column(name="note", type="text")
     * @Assert\NotNull()
     */
    private $note;
    /**
     * @ORM\Column(name="is_tax", type="boolean")
     * @Assert\NotNull()
     */
    private $isTax;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Master\Supplier", inversedBy="purchaseInvoiceHeaders")
     * @Assert\NotNull()
     */
    private $supplier;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Admin\Staff")
     * @Assert\NotNull()
     */
    private $staffCreated;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Admin\Staff")
     */
    private $staffApproved;
    /**
     * @ORM\OneToMany(targetEntity="PurchaseInvoiceDetail", mappedBy="purchaseInvoiceHeader")
     * @Assert\Valid() @Assert\Count(min=1)
     */
    private $purchaseInvoiceDetails;
    /**
     * @ORM\OneToMany(targetEntity="PurchasePaymentDetail", mappedBy="purchaseInvoiceHeader")
     */
    private $purchasePaymentDetails;
    /**
     * @ORM\OneToMany(targetEntity="PurchaseReceiptDetail", mappedBy="purchaseInvoiceHeader")
     */
    private $purchaseReceiptDetails;
    /**
     * @ORM\OneToMany(targetEntity="PurchaseReturnHeader", mappedBy="purchaseInvoiceHeader")
     */
    private $purchaseReturnHeaders;
    
    public function __construct()
    {
        $this->purchaseInvoiceDetails = new ArrayCollection();
        $this->purchasePaymentDetails = new ArrayCollection();
        $this->purchaseReceiptDetails = new ArrayCollection();
        $this->purchaseReturnHeaders = new ArrayCollection();
    }
    
    public function getCodeNumberConstant() { return 'PIN'; }
    
    public function getId() { return $this->id; }
    
    public function getTransactionDate() { return $this->transactionDate; }
    public function setTransactionDate(\DateTime $transactionDate = null) { $this->transactionDate = $transactionDate; }
    
    public function getDateApproved() { return $this->dateApproved; }
    public function setDateApproved(\DateTime $dateApproved = null) { $this->dateApproved = $dateApproved; }
    
    public function getTaxInvoiceCode() { return $this->taxInvoiceCode; }
    public function setTaxInvoiceCode($taxInvoiceCode) { $this->taxInvoiceCode = $taxInvoiceCode; }
    
    public function getSupplierInvoice() { return $this->supplierInvoice; }
    public function setSupplierInvoice($supplierInvoice) { $this->supplierInvoice = $supplierInvoice; }
    
    public function getDownpaymentType() { return $this->downpaymentType; }
    public function setDownpaymentType($downpaymentType) { $this->downpaymentType = $downpaymentType; }
    
    public function getDownpaymentValue() { return $this->downpaymentValue; }
    public function setDownpaymentValue($downpaymentValue) { $this->downpaymentValue = $downpaymentValue; }
    
    public function getDiscountType() { return $this->discountType; }
    public function setDiscountType($discountType) { $this->discountType = $discountType; }
    
    public function getDiscountValue() { return $this->discountValue; }
    public function setDiscountValue($discountValue) { $this->discountValue = $discountValue; }
    
    public function getTaxNominal() { return $this->taxNominal; }
    public function setTaxNominal($taxNominal) { $this->taxNominal = $taxNominal; }
    
    public function getShippingFee() { return $this->shippingFee; }
    public function setShippingFee($shippingFee) { $this->shippingFee = $shippingFee; }
    
    public function getSubTotal() { return $this->subTotal; }
    public function setSubTotal($subTotal) { $this->subTotal = $subTotal; }
    
    public function getGrandTotalBeforeDownpayment() { return $this->grandTotalBeforeDownpayment; }
    public function setGrandTotalBeforeDownpayment($grandTotalBeforeDownpayment) { $this->grandTotalBeforeDownpayment = $grandTotalBeforeDownpayment; }
    
    public function getGrandTotalAfterDownpayment() { return $this->grandTotalAfterDownpayment; }
    public function setGrandTotalAfterDownpayment($grandTotalAfterDownpayment) { $this->grandTotalAfterDownpayment = $grandTotalAfterDownpayment; }
    
    public function getTotalPayment() { return $this->totalPayment; }
    public function setTotalPayment($totalPayment) { $this->totalPayment = $totalPayment; }
    
    public function getTotalReturn() { return $this->totalReturn; }
    public function setTotalReturn($totalReturn) { $this->totalReturn = $totalReturn; }
    
    public function getNote() { return $this->note; }
    public function setNote($note) { $this->note = $note; }
    
    public function getIsTax() { return $this->isTax; }
    public function setIsTax($isTax) { $this->isTax = $isTax; }
    
    public function getSupplier() { return $this->supplier; }
    public function setSupplier(Supplier $supplier = null) { $this->supplier = $supplier; }
    
    public function getStaffCreated() { return $this->staffCreated; }
    public function setStaffCreated(Staff $staffCreated = null) { $this->staffCreated = $staffCreated; }
    
    public function getStaffApproved() { return $this->staffApproved; }
    public function setStaffApproved(Staff $staffApproved = null) { $this->staffApproved = $staffApproved; }
    
    public function getPurchaseInvoiceDetails() { return $this->purchaseInvoiceDetails; }
    public function setPurchaseInvoiceDetails(Collection $purchaseInvoiceDetails) { $this->purchaseInvoiceDetails = $purchaseInvoiceDetails; }
    
    public function getPurchasePaymentDetails() { return $this->purchasePaymentDetails; }
    public function setPurchasePaymentDetails(Collection $purchasePaymentDetails) { $this->purchasePaymentDetails = $purchasePaymentDetails; }
    
    public function getPurchaseReceiptDetails() { return $this->purchaseReceiptDetails; }
    public function setPurchaseReceiptDetails(Collection $purchaseReceiptDetails) { $this->purchaseReceiptDetails = $purchaseReceiptDetails; }
    
    public function getPurchaseReturnHeaders() { return $this->purchaseReturnHeaders; }
    public function setPurchaseReturnHeaders(Collection $purchaseReturnHeaders) { $this->purchaseReturnHeaders = $purchaseReturnHeaders; }
    
    public function sync()
    {
        $subTotal = 0.00;
        foreach ($this->purchaseInvoiceDetails as $purchaseInvoiceDetail) {
            $subTotal += $purchaseInvoiceDetail->getTotal();
        }
        
        $this->subTotal = $subTotal;
        $discountNominal = $this->getDiscountNominal();
        $this->taxNominal = ($this->subTotal - $discountNominal) * $this->getTaxPercentage() / 100;
        $this->grandTotalBeforeDownpayment = $this->subTotal - $discountNominal + $this->taxNominal + $this->shippingFee;
        $downpaymentNominal = $this->getDownpaymentNominal();
        $this->grandTotalAfterDownpayment = $this->grandTotalBeforeDownpayment - $downpaymentNominal;
    }
    
    public function getDiscountPercentage()
    {
        if ($this->discountType !== self::DISCOUNT_TYPE_PERCENTAGE) {
            return $this->discountValue / $this->subTotal * 100;
        } else {
            return $this->discountValue;
        }
    }
    
    public function getDiscountNominal()
    {
        if ($this->discountType !== self::DISCOUNT_TYPE_NOMINAL) {
            return $this->subTotal * $this->discountValue / 100;
        } else {
            return $this->discountValue;
        }
    }
    
    public function getDownpaymentPercentage()
    {
        if ($this->downpaymentType !== self::DOWNPAYMENT_TYPE_PERCENTAGE) {
            return $this->downpaymentValue / $this->grandTotalBeforeDownpayment * 100;
        } else {
            return $this->downpaymentValue;
        }
    }
    
    public function getDownpaymentNominal()
    {
        if ($this->downpaymentType !== self::DOWNPAYMENT_TYPE_NOMINAL) {
            return $this->grandTotalBeforeDownpayment * $this->downpaymentValue / 100;
        } else {
            return $this->downpaymentValue;
        }
    }
    
    public function getTaxPercentage()
    {
        return $this->isTax ? 10 : 0;
    }
}
