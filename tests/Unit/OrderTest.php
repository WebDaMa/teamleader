<?php

namespace Tests\Unit;

use App\Customer;
use App\Disounts\DiscountOnCategoryCheapestItemDiscount;
use App\Disounts\TotalDiscountOnTotalBoughtCustomerDiscount;
use App\Item;
use App\Logic\Math;
use App\Order;
use App\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase {

    /**
     * Test Case Discount rule:
     *
     * 10% when customer already bought 1000 euros.
     *
     * @return void
     */
    public function testTotalDiscountOnTotalBoughtCustomerDiscount()
    {
        $totalOrdered = 500;
        $totalOrder = 350;
        $percentDiscount = 10;

        $discount10PercentCustomer1000 = new TotalDiscountOnTotalBoughtCustomerDiscount(
            "Customer already bought over 1000 Euro and gets 10% on subtotal.",
            $totalOrdered, 1000, $percentDiscount, $totalOrder);

        //Check results
        $this->assertEquals(false, $discount10PercentCustomer1000->validate());

        //Test positive

        $totalOrdered = 1000;

        $discount10PercentCustomer1000 = new TotalDiscountOnTotalBoughtCustomerDiscount(
            "Customer already bought over 1000 Euro and gets 10% on subtotal.",
            $totalOrdered, 1000, $percentDiscount, $totalOrder);

        //Check results
        if($discount10PercentCustomer1000->validate()) {
            $this->assertEquals(Math::round2Decimals($totalOrder / 100 * $percentDiscount), $discount10PercentCustomer1000->calculate() );
        }

    }

    /**
     * Test Case Discount rule:
     *
     * If you buy two or more products of category "Tools" (id 1),
     * you get a 20% discount on the cheapest product.
     *
     * @return void
     */
    public function testDiscountOnCategoryCheapestItemDiscount()
    {
        $percentDiscount = 20;

        $products = factory(Product::class, 3)->make(
            [
                'category' => 1
            ]
        );

        $cheapestProduct = $products->sortBy('price')->first();

        $items = $this->makeItemsFromProducts($products, 3);

        $discount20PercentCat1OnCheapestItem = new DiscountOnCategoryCheapestItemDiscount(
            'Buy 2 or more items from category 1 and get 20% on the cheapest item.',
            20, 1, 2, $items, $cheapestProduct);

        $cheapestItem = $items->sortBy('unit_price')->first();

        if($discount20PercentCat1OnCheapestItem->validate()) {
            $this->assertEquals(Math::round2Decimals($cheapestItem->unit_price / 100 * $percentDiscount), $discount20PercentCat1OnCheapestItem->calculate() );
        }

        //Test a false case, wrong count Items

        $items = $this->makeItemsFromProducts($products, 1, 1);

        $discount20PercentCat1OnCheapestItem = new DiscountOnCategoryCheapestItemDiscount(
            'Buy 2 or more items from category 1 and get 20% on the cheapest item.',
            20, 1, 2, $items, $cheapestProduct);


        $this->assertEquals(false, $discount20PercentCat1OnCheapestItem->validate());

        //Test a false case, wrong category
        $products = factory(Product::class, 3)->make(
            [
                'category' => 3
            ]
        );

        $cheapestProduct = $products->sortBy('price')->first();

        $items = $this->makeItemsFromProducts($products, 3);

        $discount20PercentCat1OnCheapestItem = new DiscountOnCategoryCheapestItemDiscount(
            'Buy 2 or more items from category 1 and get 20% on the cheapest item.',
            20, 1, 2, $items, $cheapestProduct);


        $this->assertEquals(false, $discount20PercentCat1OnCheapestItem->validate());


    }

    private function makeItemsFromProducts($products, $countItems, $quantity = 0) {
        if($quantity === 0) {
            $quantity = rand(1, 10);
        }
        $i = 0;

        return $items = factory(Item::class, $countItems)->make([
            'order_id' => 1,
            'product_id' => 1,
            'quantity' => $quantity
        ])->each(function ($item) use (&$i, $products)
        {
            $item->product_id = $products->values()->get($i)->product_id;
            $item->unit_price = $products->values()->get($i)->price;
            $item->total = $products->values()->get($i)->price * $item->quantity;
            $item->subtotal = $products->values()->get($i)->price * $item->quantity;
            $i++;
        });
    }

}
