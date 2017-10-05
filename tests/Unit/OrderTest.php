<?php

namespace Tests\Unit;

use App\Customer;
use App\Item;
use App\Order;
use App\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase {

    /**
     * Test Case 1 Discount rule:
     *
     * 10% when customer already bought 1000 euros.
     *
     * @return void
     */
    public function testCalculateDiscountCustomer1000()
    {
        //Overide Customer, so we can test multiple orders
        $customer = factory(Customer::class)
            ->create();

        //Make 10 orders for the same customer
        $order = null;
        $i = 0;
        $orders = factory(Order::class, 10)
            ->create([
                'customer_id' => $customer->id
            ])
            ->each(function ($o)
            {
                /**
                 * @var $o Order
                 */
                $items = factory(Item::class, rand(1, 3))->create([
                    'order_id' => $o->id,
                ])->each(function ($item) use (&$o, &$i)
                {
                    $o->subtotal += $item->subtotal;
                });
                $o->total = $o->subtotal;
                $o->save();

                $o->calculateDiscountCustomer1000();
                $o->calculateDiscountPercentage();

                $totalOrdered = Order::where('customer_id', $o->customer_id)
                    //Don't include current order
                    ->where('order_id', '!=', $o->order_id)
                    ->sum('total');

                if ($totalOrdered > 1000)
                {
                    // If Customer has already bought more than 1000 give 10% discount
                    $this->assertEquals($o->total, $o->round2Decimals(
                        $o->subtotal -
                        $o->round2Decimals($o->subtotal / 100 * 10)
                    ));
                } else
                {
                    $this->assertEquals($o->total, $o->subtotal);
                }
            });

    }

    /**
     * Test Case 3 Discount rule:
     *
     * If you buy two or more products of category "Tools" (id 1),
     * you get a 20% discount on the cheapest product.
     *
     * @return void
     */
    public function testCalculateDiscountCat1()
    {
        /**
         * @var $order Order
         */
        $order = factory(Order::class)->create();

        $products = factory(Product::class, 3)->create(
            [
                'category' => 1
            ]
        );

        $i = 0;
        $items = factory(Item::class, 3)->create([
            'order_id' => $order->id,
        ])->each(function ($item) use (&$order, &$i, $products)
        {
            $item->product_id = $products->values()->get($i)->product_id;
            $item->save();
            $order->subtotal += $item->subtotal;
            $i++;
        });
        $order->total = $order->subtotal;
        $order->save();

        $order->calculateDiscountCat1();
        $order->calculateDiscountItems();

        $cat1ItemsCount = $items->sum('quantity');

        if ($cat1ItemsCount >= 2)
        {
            /**
             * @var $cheapestCat1Item Item
             */
            $cheapestCat1Item = $items->sortBy('unit_price')->first();

            //var_dump('t');
            //Test if subtotal order minus discount cheapest item is equal to total order
            $this->assertEquals($order->total, $order->round2Decimals(
                $order->subtotal - ($cheapestCat1Item->unit_price / 100 * 20))
            );
        } else
        {
            $this->assertEquals($order->total, $order->subtotal);
        }
    }

}
