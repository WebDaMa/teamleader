<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Item::class, function (Faker $faker) {
    $quantity = rand(1,10);
    $up = rand(100,1000);
    return [
        'product_id' => function () use($up) {
            return factory(App\Product::class)->create(
                ['price' => $up]
            )->product_id;
        },
        'quantity' => $quantity,
        'unit_price' => $up,
        'subtotal' => $up * $quantity,
        'discount' => 0,
        'discount_and_reason' => '',
        'total' => $up * $quantity,
    ];
});
