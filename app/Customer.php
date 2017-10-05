<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model {

    /**
     * Get the orders for the customer.
     */
    public function orders()
    {
        return $this->hasMany('App\Order');
    }
}
