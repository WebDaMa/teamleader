<?php

namespace Tests\Feature;

use App\Item;
use App\Order;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase {

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testInsertOrderWithItems()
    {
        /**
         * @var $order Order
         */
        $order = factory(Order::class)->make();
        $order->id = 1;

        //Add the items
        $items = factory(Item::class, 3)->make([
            'order_id' => $order->id,
        ])->each(function ($item) use (&$order)
        {
            //Update the totals
            $order->subtotal += $item->subtotal;
        });

        $order->items = $items;

        $order->total = $order->subtotal;

        $orderArr = $order->toArray();

        $orderArr['items'] = $order->items->toArray();

        //Change some keys to map json format

        foreach ($orderArr['items'] as $k =>$item) {
            $item['product-id'] = $item['product_id'];
            unset($item['product_id']);

            $item['unit-price'] = $item['unit_price'];
            unset($item['unit_price']);

            $orderArr['items'][$k] = $item;
        }

        $orderArr['customer-id'] = $orderArr['customer_id'];
        unset($orderArr['customer_id']);

        $response = $this->json('POST', '/api/orders',
            $orderArr
        );

        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'customer-id',
                    'items',
                    'subtotal',
                    'discount',
                    'discount-and-reason',
                    'total',
                ]
            ]);

    }
}