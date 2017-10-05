<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {

    /**
     * Get the items for the product.
     */
    public function items()
    {
        return $this->hasMany('App\Item');
    }
}
