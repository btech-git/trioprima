<?php

namespace AppBundle\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Common\CodeNumberEntity;
use AppBundle\Entity\Master\Customer;
use AppBundle\Entity\Admin\Staff;

/**
 * @ORM\Table(name="transaction_sale_invoice_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Transaction\SaleInvoiceHeaderRepository")
 */
class SaleInvoiceHeader extends CodeNumberEntity
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
     * @ORM\Column(name="customer_invoice", type="string", length=60)
     * @Assert\NotNull()
     */
    private $customerInvoice;
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Master\Customer", inversedBy="saleInvoiceHeaders")
     * @Assert\NotNull()
     */
    private $customer;
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
     * @ORM\OneToMany(targetEntity="SaleInvoiceDetail", mappedBy="saleInvoiceHeader")
     * @Assert\Valid() @Assert\Count(min=1)
     */
    private $saleInvoiceDetails;
    /**
     * @ORM\OneToMany(targetEntity="SalePaymentDetail", mappedBy="saleInvoiceHeader")
     */
    private $salePaymentDetails;
    /**
     * @ORM\OneToMany(targetEntity="SaleReceiptDetail", mappedBy="saleInvoiceHeader")
     */
    private $saleReceiptDetails;
    /**
     * @ORM\OneToMany(targetEntity="SaleReturnHeader", mappedBy="saleInvoiceHeader")
     */
    private $saleReturnHeaders;
    
    public function __construct()
    {
        $this->saleInvoiceDetails = new ArrayCollection();
        $this->salePaymentDetails = new ArrayCollection();
        $this->saleReceiptDetails = new ArrayCollection();
        $this->saleReturnHeaders = new ArrayCollection();
    }
    
    public function getCodeNumberConstant() { return 'INV'; }
    
    public function getId() { return $this->id; }
    
    public function getTransactionDate() { return $this->transactionDate; }
    public function setTransactionDate(\DateTime $transactionDate = null) { $this->transactionDate = $transactionDate; }
    
    public function getDateApproved() { return $this->dateApproved; }
    public function setDateApproved(\DateTime $dateApproved = null) { $this->dateApproved = $dateApproved; }
    
    public function getTaxInvoiceCode() { return $this->taxInvoiceCode; }
    public function setTaxInvoiceCode($taxInvoiceCode) { $this->taxInvoiceCode = $taxInvoiceCode; }
    
    public function getCustomerInvoice() { return $this->customerInvoice; }
    public function setCustomerInvoice($customerInvoice) { $this->customerInvoice = $customerInvoice; }
    
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
    
    public function getCustomer() { return $this->customer; }
    public function setCustomer(Customer $customer = null) { $this->customer = $customer; }
    
    public function getStaffCreated() { return $this->staffCreated; }
    public function setStaffCreated(Staff $staffCreated = null) { $this->staffCreated = $staffCreated; }
    
    public function getStaffApproved() { return $this->staffApproved; }
    public function setStaffApproved(Staff $staffApproved = null) { $this->staffApproved = $staffApproved; }
    
    public function getSaleInvoiceDetails() { return $this->saleInvoiceDetails; }
    public function setSaleInvoiceDetails(Collection $saleInvoiceDetails) { $this->saleInvoiceDetails = $saleInvoiceDetails; }
    
    public function getSalePaymentDetails() { return $this->salePaymentDetails; }
    public function setSalePaymentDetails(Collection $salePaymentDetails) { $this->salePaymentDetails = $salePaymentDetails; }
    
    public function getSaleReceiptDetails() { return $this->saleReceiptDetails; }
    public function setSaleReceiptDetails(Collection $saleReceiptDetails) { $this->saleReceiptDetails = $saleReceiptDetails; }
    
    public function getSaleReturnHeaders() { return $this->saleReturnHeaders; }
    public function setSaleReturnHeaders(Collection $saleReturnHeaders) { $this->saleReturnHeaders = $saleReturnHeaders; }
    
    public function sync()
    {
        if ($this->transactionDate !== null) {
            $this->setCodeNumberMonth(intval($this->transactionDate->format('m')));
            $this->setCodeNumberYear(intval($this->transactionDate->format('y')));
        }
        
        $subTotal = 0.00;
        foreach ($this->saleInvoiceDetails as $saleInvoiceDetail) {
            $subTotal += $saleInvoiceDetail->getTotal();
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
    
    public function getAveragePurchaseGrandTotal()
    {
        $total = 0.00;
        foreach ($this->saleInvoiceDetails as $saleInvoiceDetail) {
            $total += $saleInvoiceDetail->getAveragePurchaseTotal();
        }
        
        return $total;
    }
    
    public function getProfitLoss()
    {
        return $this->grandTotalBeforeDownpayment - $this->getAveragePurchaseGrandTotal();
    }
}
