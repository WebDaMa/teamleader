<?php

namespace App\Disounts\Item;

use App\Interfaces\Discount;

class FreeItemOnTotalItemsCategoryDiscount extends ItemDiscount implements Discount{
    //Case 2: For every product of category "Switches" (id 2), when you buy five, you get a sixth for free.

    private $categoryIdItem;
    private $countFreeItems;
    private $countItemsNeeded;
    private $product;
    private $item;

    /**
     * FreeItemOnTotalItemsCategoryDiscount constructor.
     * @param $reason
     * @param $categoryIdItem
     * @param $countFreeItems
     * @param $product
     * @param $item
     */
    public function __construct($reason, $categoryIdItem, $countItemsNeeded, $countFreeItems, $product, $item)
    {
        $this
            ->setReason($reason)
            ->setCategoryIdItem($categoryIdItem)
            ->setCountItemsNeeded($countItemsNeeded)
            ->setCountFreeItems($countFreeItems)
            ->setProduct($product)
            ->setItem($item);
    }

    public function getCategoryIdItem() {
        return $this->categoryIdItem;
    }

    public function setCategoryIdItem($categoryIdItem) {
        $this->categoryIdItem = $categoryIdItem;
        return $this;
    }

    public function getCountFreeItems() {
        return $this->countFreeItems;
    }

    public function setCountFreeItems($countFreeItems) {
        $this->countFreeItems = $countFreeItems;
        return $this;
    }

    public function getCountItemsNeeded() {
        return $this->countItemsNeeded;
    }

    public function setCountItemsNeeded($countItemsNeeded) {
        $this->countItemsNeeded = $countItemsNeeded;
        return $this;
    }

    public function getProduct() {
        return $this->product;
    }

    public function setProduct($product) {
        $this->product = $product;
        return $this;
    }

    public function getItem() {
        return $this->item;
    }

    public function setItem($item) {
        $this->item = $item;
        return $this;
    }

    public function calculate()
    {
        return $this->getItem()->unit_price * $this->getCountFreeItems();
    }

    public function validate()
    {
        if ($this->getProduct()->category == $this->categoryIdItem
            && $this->getItem()->quantity >= $this->getCountItemsNeeded())
        {
            return true;
        }
        return false;
    }
}