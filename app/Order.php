<?php

namespace App;

use App\Disounts\DiscountOnCategoryCheapestItemDiscount;
use App\Disounts\TotalDiscountOnTotalBoughtCustomerDiscount;
use App\Interfaces\Discount;
use App\Logic\Math;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    private $overallPercentDiscount = [];
    public $discountAndReason = [];

    private $discounts = [];

    /**
     * Get the items for the order.
     */
    public function items()
    {
        return $this->hasMany('App\Item');
    }

    /**
     * Get the customer that owns the order.
     */
    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }

    /**
     * Calculate the Discount for an order and its items
     * @return Order
     */
    public function calculateDiscount()
    {
        $this->calculateDiscountItems();

        /*
         * Case: A customer who has already bought for over € 1000,
         * gets a discount of 10% on the whole order.
         */

        $totalOrdered = $this->getTotalOrdered();

        $discount10PercentCustomer1000 = new TotalDiscountOnTotalBoughtCustomerDiscount(
            "Customer already bought over 1000 Euro and gets 10% on subtotal.",
            $totalOrdered, 1000, 10, $this->subtotal);

        $this->discounts[] = $discount10PercentCustomer1000;

        /*
         * Case: If you buy two or more products of category "Tools" (id 1),
         *  you get a 20% discount on the cheapest product.
         */

        $cat1Items = $this->getItemsForCategory(1);
        $cheapestProduct = $this->getCheapestProductFromItems($cat1Items);

        $discountCat1 = new DiscountOnCategoryCheapestItemDiscount(
            'Buy 2 or more items from category 1 and get 20% on the cheapest item.',
            20, 1, 2, $cat1Items, $cheapestProduct);
        $this->discounts[] = $discountCat1;

        $this->calculateDiscountOrder();

        return $this;
    }

    public function calculateDiscountItems()
    {
        $discount = 0;
        $this->items->each(function ($item) use (&$discount)
        {
            // Calculate discount for Item
            /**
             * @var $item Item
             */

            $updatedItem = $item->calculateDiscount();

            $discount += $updatedItem->discount;
        });

        $this->subtotal = Math::round2Decimals($this->subtotal - $discount);
        $this->save();
    }

    /*
     * @return Order
     */
    public function calculateDiscountOrder()
    {

        $totalDiscount = 0;

        foreach ($this->discounts as $discount)
        {
            /**
             * @var $discount Discount
             */
            if ($discount->validate())
            {
                $discountValue = $discount->calculate();
                $this->discountAndReason[] = [
                    'reason' => $discount->getReason(),
                    'discount' => $discountValue
                ];
                $totalDiscount += $discountValue;
            }
        }

        $this->addDiscountAndReason();
        $this->discount = Math::round2Decimals($totalDiscount);
        $this->total = Math::round2Decimals($this->subtotal - $this->discount);

        // Store Cheapest item
        $this->save();

        return $this;
    }

    /**
     * @param $discountAndReason array key is reason and value the total discount
     */
    private function addDiscountAndReason()
    {
        $discountAndReasonArr = json_decode($this->discount_and_reason);

        if (is_array($discountAndReasonArr))
        {
            $discountAndReasonArr[] = $this->discountAndReason;
        } else
        {
            $discountAndReasonArr = $this->discountAndReason;
        }

        $this->discount_and_reason = json_encode($discountAndReasonArr);


        $this->discountAndReason = [];
    }

    public function getItemsForCategory($categoryId)
    {
        return Item::join('products', 'products.product_id', '=', 'items.product_id')
            ->where('order_id', $this->id)
            ->where('products.category', $categoryId)
            ->select('items.*')
            ->get();

    }

    /**
     * @return float Returns the total amount a customer has ordered
     */
    public function getTotalOrdered()
    {
        return Order::where('customer_id', $this->customer_id)
            //Don't include current order
            ->where('order_id', '!=', $this->order_id)
            ->sum('total');
    }

    public function getCheapestProductFromItems($items) {
        return $items->sortBy('unit_price')->first()->getProduct();
    }

}
