<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase {

    /**
     * Tests a mass insert of Product
     *
     * @return void
     */
    public function testMassInsert()
    {
        $response = $this->json('POST', '/api/products/mass',
            json_decode('[ { "id": "A101", "description": "Screwdriver", "category": "1", "price": "9.75" }, { "id": "A102", "description": "Electric screwdriver", "category": "1", "price": "49.50" }, { "id": "B101", "description": "Basic on-off switchs", "category": "2", "price": "4.99" }, { "id": "B102", "description": "Press button", "category": "2", "price": "4.99" }, { "id": "B103", "description": "Switch with motion detector", "category": "2", "price": "12.95" } ]')
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                "message" => "Product Bulk insert successful!",
            ]);
    }
}
