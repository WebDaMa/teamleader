<?php

namespace Tests\Unit;

use App\Disounts\Item\FreeItemOnTotalItemsCategoryDiscount;
use App\Item;
use App\Order;
use App\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemTest extends TestCase
{
    /**
     * Case:
     *
     * For every product of category "Switches" (id 2), when you buy five,
     * you get a sixth for free.
     *
     * @return void
     */
    public function testFreeItemOnTotalItemsCategoryDiscount()
    {
        $price = 100;

        $productCat2 = factory(Product::class)->make(
            [
                'category' => 2,
                'price' => $price
            ]
        );

        $quantity = 6;
        $itemCat2 = factory(Item::class)->make([
            'quantity' => $quantity,
            'unit_price' => $price,
            'subtotal' => $quantity * $price,
            'total' => $quantity * $price,
            'product_id' => $productCat2->product_id
        ]);

        $itemsNeeded = 6;
        $freeItems = 1;

        $freeItemCat2When5Bought = new FreeItemOnTotalItemsCategoryDiscount(
            'Buy 5 items from category 2 and get the sixth item for free.',
            2, $itemsNeeded, $freeItems, $productCat2, $itemCat2);

        //Check results
        if($freeItemCat2When5Bought->validate()) {
            $this->assertEquals((($itemsNeeded - $quantity + 1) * $freeItems) * $itemCat2->unit_price , $freeItemCat2When5Bought->calculate() );
        }else{
            $this->assertEquals($freeItemCat2When5Bought->validate() , false );
        }

        //Test a false validation on wrong category

        $productCat2 = factory(Product::class)->make(
            [
                'category' => 3,
                'price' => $price
            ]
        );

        $itemCat2 = factory(Item::class)->make([
            'quantity' => $quantity,
            'unit_price' => $price,
            'subtotal' => $quantity * $price,
            'total' => $quantity * $price,
            'product_id' => $productCat2->product_id
        ]);

        $freeItemCat2When5Bought = new FreeItemOnTotalItemsCategoryDiscount(
            'Buy 5 items from category 2 and get the sixth item for free.',
            2, $itemsNeeded, $freeItems, $productCat2, $itemCat2);

        //Check results
        $this->assertEquals($freeItemCat2When5Bought->validate() , false );

        //Test a false validation on quantity

        $productCat2 = factory(Product::class)->make(
            [
                'category' => 2,
                'price' => $price
            ]
        );
        $quantity = 4;

        $itemCat2 = factory(Item::class)->make([
            'quantity' => $quantity,
            'unit_price' => $price,
            'subtotal' => $quantity * $price,
            'total' => $quantity * $price,
            'product_id' => $productCat2->product_id
        ]);

        $freeItemCat2When5Bought = new FreeItemOnTotalItemsCategoryDiscount(
            'Buy 5 items from category 2 and get the sixth item for free.',
            2, $itemsNeeded, $freeItems, $productCat2, $itemCat2);

        //Check results
        $this->assertEquals($freeItemCat2When5Bought->validate() , false );
    }
}
