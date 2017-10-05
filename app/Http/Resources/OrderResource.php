<?php

namespace App\Http\Resources;

use App\Item;
use App\Order;
use Illuminate\Http\Resources\Json\Resource;

class OrderResource extends Resource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {

        $items = [];

        foreach ($this->items as $item)
        {
            $items[] = new ItemResource($item);
        }

        return [
            'id' => $this->order_id,
            'customer-id' => $this->customer_id,
            'items' => $items,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'discount-and-reason' => json_decode($this->discount_and_reason, true),
            'total' => $this->total
        ];
    }
}
