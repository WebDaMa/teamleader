<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model {

    public $discountAndReason = [];

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
        $this->calculateDiscountQuantityCat2();

        return $this;
    }

    /*
     * Calculate a discount based on quantity
     * @return Item
     */
    public function calculateDiscountQuantityCat2()
    {
        //Case 2: For every product of category "Switches" (id 2), when you buy five, you get a sixth for free.
        $product = Product::where('product_id', $this->product_id)->first();
        if ($product && $product->category === 2 && $this->quantity >= 6)
        {
            $this->discount += $this->unit_price;
            $this->discountAndReason[] = [
                'reason' => 'Buy 5 get sixth for free.',
                'discount' => $this->unit_price
            ];
            $this->addDiscountAndReason();

            $this->total = $this->subtotal - $this->discount;

            //Save the item
            $this->save();

            return $this;
        }
    }

    /*
     * @param $percentage assoc array of all percentages with reason as key, 20% is 20
     * @return Item
     */
    public function calculateDiscountCheapestPercentageCat1($percentages)
    {
        $totalPercent = 0;
        foreach ($percentages as $reason => $percentage)
        {
            $totalPercent += $percentage;
            $this->discountAndReason[] = [
                'reason' => $reason,
                'discount' => $this->round2Decimals($this->unit_price / 100 * $percentage)
            ];
        }
        $this->addDiscountAndReason();

        $this->discount += $this->round2Decimals($this->unit_price / 100 * $totalPercent);

        $this->total = $this->round2Decimals($this->subtotal - $this->discount);
        $this->save();
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
     * @param $float float
     * @return string
     */
    public function round2Decimals($float)
    {
        return number_format((float) $float, 2, '.', '');
    }
}
