<?php

namespace Tests\Unit;

use App\Item;
use App\Order;
use App\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemTest extends TestCase
{
    /**
     * Case 2:
     *
     * For every product of category "Switches" (id 2), when you buy five,
     * you get a sixth for free.
     *
     * @return void
     */
    public function testCalculateDiscountQuantityCat2()
    {
        /**
         * @var $order Order
         */
        $order = factory(Order::class)->create();

        $productsCat2 = factory(Product::class, 2)->create(
            [
                'category' => 2
            ]
        );

        $productCat1 = factory(Product::class)->create(
            [
                'category' => 1
            ]
        );

        $i = 0;
        $itemCat2Discount = factory(Item::class)->create([
            'order_id' => $order->id,
            'quantity' => rand(5,10)
        ]);
        /**
         * @var $itemCat2Discount Item
         */
        $order = $this->updateItemProductAndOrderSubtotal($productsCat2, $i, $itemCat2Discount, $order);

        $i++;

        $itemCat2NoDiscount = factory(Item::class)->create([
            'order_id' => $order->id,
            'quantity' => rand(1,4)
        ]);
        /**
         * @var $itemCat2NoDiscount Item
         */
        $order = $this->updateItemProductAndOrderSubtotal($productsCat2, $i, $itemCat2NoDiscount, $order);

        $itemCat1 = factory(Item::class)->create([
            'order_id' => $order->id,
            'quantity' => rand(1,10)
        ]);

        $order = $this->updateItemProductAndOrderSubtotal(collect([$productCat1]), 0, $itemCat1, $order);

        $order->total = $order->subtotal;
        $order->save();

        //Only do this test on item
        $itemCat2Discount = $this->calculateDiscountItem($itemCat2Discount, $order);
        $order = $this->calculateDiscountOrderFromItem($itemCat2Discount, $order);

        $itemCat2NoDiscount = $this->calculateDiscountItem($itemCat2NoDiscount, $order);
        $order = $this->calculateDiscountOrderFromItem($itemCat2NoDiscount, $order);

        $itemCat1 = $this->calculateDiscountItem($itemCat1, $order);
        $order = $this->calculateDiscountOrderFromItem($itemCat1, $order);

        $order->save();

        //Check results

        $this->assertEquals($itemCat2Discount->total, $itemCat2Discount->subtotal - $itemCat2Discount->unit_price );
        $this->assertEquals($itemCat2NoDiscount->total, $itemCat2NoDiscount->subtotal );
        $this->assertEquals($itemCat1->total, $itemCat1->subtotal );
    }

    /**
     * @param $products
     * @param $i
     * @param $itemCat2NoDiscount
     * @param $order
     * @return Order
     */
    private function updateItemProductAndOrderSubtotal($products, $i, $itemCat2NoDiscount, $order)
    {
        $itemCat2NoDiscount->product_id = $products->values()->get($i)->product_id;
        $itemCat2NoDiscount->save();
        $order->subtotal += $itemCat2NoDiscount->subtotal;

        return $order;
    }

    /**
     * @param $item Item
     * @param $order Order
     */
    private function calculateDiscountItem($item, $order)
    {
        $item->calculateDiscountQuantityCat2();
        return $item;
    }

    /**
     * @param $item Item
     * @param $order Order
     */
    private function calculateDiscountOrderFromItem($item, $order)
    {
        $order->discount += $item->discount;
        $order->total -= $item->discount;
        return $order;
    }

}
