<?php

namespace App\Interfaces;

interface Discount {

    //Item vs total order?

    /*$discountPercentage;
    $discountValue;
    $discountCategory;
    $discountTotalBought;*/

    //Single class
    /*$discountFreeItems;
    $discountFreeItemsOnCount;*/

    public function calculate();

    public function validate();

    public function getReason();

    public function setReason($reason = '');

}