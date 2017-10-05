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

$factory->define(App\Order::class, function (Faker $faker) {
    return [
        'order_id' => $faker->randomDigitNotNull,
        'customer_id' => function () {
            return factory(App\Customer::class)->create()->id;
        },
        'subtotal' => 0,
        'discount' => 0,
        'discount_and_reason' => '',
        'total' => 0,
    ];
});
