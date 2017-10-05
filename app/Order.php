<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    private $overallPercentDiscount = [];
    public $discountAndReason = [];

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

        /*
         * Case 1: A customer who has already bought for over € 1000, gets a discount of 10% on the whole order.
         */

        $this->calculateDiscountCustomer1000();

        /*
         * Case 3: If you buy two or more products of category "Tools" (id 1), you get a 20% discount on the cheapest product.
         */

        $this->calculateDiscountCat1();

        //Keep this at end, so discounts are all calculated for special group cases
        $this->calculateDiscountItems();

        // Calculate Discount and total
        $this->calculateDiscountPercentage();

        return $this;
    }

    public function calculateDiscountCustomer1000()
    {
        // First lets get all orders from the customer:
        $totalOrdered = Order::where('customer_id', $this->customer_id)
            //Don't include current order
            ->where('order_id', '!=', $this->order_id)
            ->sum('total');

        if ($totalOrdered > 1000)
        {
            $this->addPercentageAndReason(10,
                "Customer already bought over 1000 Euro and gets 10% on subtotal");
        }
    }

    public function calculateDiscountCat1()
    {
        /**
         * @var $cat1Items Collection
         */
        $cat1Items = Item::join('products', 'products.product_id', '=', 'items.product_id')
            ->where('order_id', $this->id)
            ->where('products.category', 1)
            ->select('items.*')
            ->get();

        $cat1ItemsCount = $cat1Items->sum('quantity');

        if ($cat1ItemsCount >= 2)
        {
            /**
             * @var $cheapestCat1Item Item
             */
            $cheapestCat1Item = $cat1Items->sortBy('unit_price')->first();
            $cheapestCat1Item->calculateDiscountCheapestPercentageCat1(['Buy 2 or more from this category, get 20% on cheapest' => 20]);
        }
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

        $this->discount += $discount;
        $this->total = $this->round2Decimals($this->subtotal - $this->discount);
        $this->save();
    }

    /*
     * @return Order
     */
    public function calculateDiscountPercentage()
    {
        $totalPercent = 0;
        foreach ($this->overallPercentDiscount as $percentageValues)
        {
            $totalPercent += $percentageValues['percentage'];
            //We seperate these to know the percent and discount for each reason
            $this->discountAndReason[] = [
                'reason' => $percentageValues['reason'],
                'discount' => $this->round2Decimals($this->subtotal / 100 * $percentageValues['percentage'])
            ];
        }
        $this->addDiscountAndReason();
        $this->discount = $this->round2Decimals($this->subtotal / 100 * $totalPercent + $this->discount);
        $this->total = $this->round2Decimals($this->subtotal - $this->discount);

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

    /**
     * @param $percentage float
     * @param $reason string
     */
    private function addPercentageAndReason($percentage, $reason)
    {
        $this->overallPercentDiscount[] = [
            "percentage" => $percentage,
            "reason" => $reason
        ];
    }

    /**
     * @param $float float
     * @return string
     */
    public function round2Decimals($float)
    {
        return number_format((float) $float, 2, '.', '');
    }
}
