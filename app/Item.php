<?php

namespace App;

use App\Disounts\Item\FreeItemOnTotalItemsCategoryDiscount;
use App\Interfaces\Discount;
use App\Logic\Math;
use Illuminate\Database\Eloquent\Model;

class Item extends Model {

    public $discountAndReason = [];
    private $discounts = [];

    /**
     * Get the order that owns the item.
     */
    public function product()
    {
        return $this->belongsTo('App\Product');
    }

    /**
     * Get the order that owns the item.
     */
    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public function calculateDiscount()
    {
        //Add more discount rules here
        $freeItemCat2When5Bought = new FreeItemOnTotalItemsCategoryDiscount(
            'Buy 5 items from category 2 and get the sixth item for free.',
            2, 6, 1, $this->getProduct(), $this);

        $this->discounts[] = $freeItemCat2When5Bought;

        $this->calculateDiscountItem();

        return $this;
    }

    /*
     * @return Order
     */
    public function calculateDiscountItem()
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

        //Save the item
        $this->save();
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

    public function getProduct() {
        return Product::where('product_id', $this->product_id)->first();
    }
}
