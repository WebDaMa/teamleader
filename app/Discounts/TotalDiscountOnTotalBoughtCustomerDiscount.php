<?php

namespace App\Disounts;

use App\Interfaces\Discount;
use App\Logic\Math;

class TotalDiscountOnTotalBoughtCustomerDiscount extends TotalDiscount implements Discount {

    private $totalBoughtForDiscountCustomer;
    private $totalBoughtCustomer;
    private $totalPercentageOnTotalBought;
    private $totalOrder;

    /**
     * TotalDiscountOnTotalBoughtCustomerDiscount constructor.
     * @param $reason
     * @param $totalBoughtCustomer
     * @param $totalBoughtForDiscountCustomer
     * @param $totalPercentageOnTotalBought
     * @param $totalOrder
     */
    public function __construct($reason, $totalBoughtCustomer, $totalBoughtForDiscountCustomer, $totalPercentageOnTotalBought, $totalOrder)
    {
        $this
            ->setReason($reason)
            ->setTotalBoughtCustomer($totalBoughtCustomer)
            ->setTotalBoughtForDiscountCustomer($totalBoughtForDiscountCustomer)
            ->setTotalPercentageOnTotalBought($totalPercentageOnTotalBought)
            ->setTotalOrder($totalOrder);
    }

    public function getTotalBoughtForDiscountCustomer() {
        return $this->totalBoughtForDiscountCustomer;
    }

    public function setTotalBoughtForDiscountCustomer($totalBoughtForDiscountCustomer = 0) {
        $this->totalBoughtForDiscountCustomer = $totalBoughtForDiscountCustomer;
        return $this;
    }

    public function getTotalBoughtCustomer() {
        return $this->totalBoughtCustomer;
    }

    public function setTotalBoughtCustomer($totalBoughtCustomer = 0) {
        $this->totalBoughtCustomer = $totalBoughtCustomer;
        return $this;
    }

    public function getTotalOrder() {
        return $this->totalOrder;
    }

    public function setTotalOrder($totalOrder = 0) {
        $this->totalOrder = $totalOrder;
        return $this;
    }

    public function getTotalPercentageOnTotalBought() {
        return $this->totalPercentageOnTotalBought;
    }

    public function setTotalPercentageOnTotalBought($totalPercentageOnTotalBought = 0) {
        $this->totalPercentageOnTotalBought = $totalPercentageOnTotalBought;
        return $this;
    }

    public function calculate()
    {
        return Math::round2Decimals($this->getTotalOrder() / 100 * $this->getTotalPercentageOnTotalBought());
    }

    public function validate()
    {

        if ($this->getTotalBoughtCustomer() >= $this->getTotalBoughtForDiscountCustomer())
        {
            return true;
        }

        return false;
    }
}