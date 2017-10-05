<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ItemResource extends Resource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'product-id' => $this->product_id,
            'quantity' => $this->quantity,
            'unit-price' => $this->unit_price,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'discount-and-reason' => json_decode($this->discount_and_reason, true),
            'total' => $this->total,
        ];
    }
}
