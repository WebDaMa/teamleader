<?php

namespace App\Disounts;

use App\Interfaces\Discount;
use App\Logic\Math;

class DiscountOnCategoryCheapestItemDiscount extends TotalDiscount implements Discount {

    private $percentDiscount;
    private $categoryIdItem;
    private $items;
    private $countItemsNeededForDiscount;
    private $cheapestProduct;

    /**
     * DiscountOnCategoryCheapestItemDiscount constructor.
     * @param $reason
     * @param $percentDiscount
     * @param $categoryIdItem
     * @param $countItemsNeededForDiscount
     * @param $items
     */
    public function __construct($reason, $percentDiscount, $categoryIdItem, $countItemsNeededForDiscount, $items, $cheapestProduct)
    {
        $this
            ->setReason($reason)
            ->setPercentDiscount($percentDiscount)
            ->setCategoryIdItem($categoryIdItem)
            ->setCountItemsNeededForDiscount($countItemsNeededForDiscount)
            ->setItems($items)
            ->setCheapestProduct($cheapestProduct);
    }

    public function getPercentDiscount() {
        return $this->percentDiscount;
    }

    public function setPercentDiscount($percentDiscount) {
        $this->percentDiscount = $percentDiscount;
        return $this;
    }

    public function getCountItemsNeededForDiscount() {
        return $this->countItemsNeededForDiscount;
    }

    public function setCountItemsNeededForDiscount($countItemsNeededForDiscount) {
        $this->countItemsNeededForDiscount = $countItemsNeededForDiscount;
        return $this;
    }

    public function getCategoryIdItem() {
        return $this->categoryIdItem;
    }

    public function setCategoryIdItem($categoryIdItem) {
        $this->categoryIdItem = $categoryIdItem;
        return $this;
    }

    public function getItems() {
        return $this->items;
    }

    public function setItems($items) {
        $this->items = $items;
        return $this;
    }

    public function getCheapestProduct() {
        return $this->cheapestProduct;
    }

    public function setCheapestProduct($cheapestProduct) {
        $this->cheapestProduct = $cheapestProduct;
        return $this;
    }

    private function getCheapestItem() {
        return $this->getItems()->sortBy('unit_price')->first();
    }

    public function calculate()
    {
        return Math::round2Decimals($this->getCheapestItem()->unit_price / 100 * $this->getPercentDiscount());
    }

    public function validate()
    {
        $itemsCount = $this->items->sum('quantity');

        if ($this->getCheapestProduct()->category == $this->getCategoryIdItem() && $itemsCount >= $this->getCountItemsNeededForDiscount())
        {
            return true;
        }

        return false;
    }
}