<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Http\Resources\OrderResource;
use App\Item;
use App\Order;
use App\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderController extends Controller {

    /**
     * @param $id
     * @return OrderResource
     */
    public function show($id)
    {
        if (!$id)
        {
            throw new HttpException(400, "Invalid id");
        }

        return new OrderResource(Order::find($id));
    }

    /**
     * @param Request $request
     * @return OrderResource
     */
    public function store(Request $request)
    {

        $id = $request->input('id');


        if (isset($id))
        {
            // Check for update
            $order = Order::where('order_id', $id)->first();
            if (is_null($order))
            {
                // no update
                $customer = Customer::where('id', $request->input('customer-id'))->first();

                if (is_null($customer))
                {
                    throw new HttpException(400, "Customer " . $request->input('customer-id') . " doesn't exists. Import product first.");
                }
                $order = new Order;
            }

            $order->order_id = $id;
            $order->customer_id = $request->input('customer-id');
            $order->subtotal = $request->input('total');
            $order->discount = 0;
            $order->discount_and_reason = '';
            $order->total = $order->subtotal;

            $saved = $order->save();


            $items = $request->input('items');

            foreach ($items as $itemValues)
            {
                // Check for update
                $item = Item::where('order_id', $order->id)
                    ->where('product_id', $itemValues["product-id"])
                    ->where('quantity', $itemValues["quantity"])
                    ->where('unit_price', $itemValues["unit-price"])
                    ->first();

                if (is_null($item))
                {
                    // no update
                    //Check if product exists

                    $product = Product::where('product_id', $itemValues["product-id"])->first();

                    if (is_null($product))
                    {
                        throw new HttpException(400, "Product " . $itemValues["product-id"] . " doesn't exists. Import product first.");
                    }

                    $item = new Item;
                }

                $item->order_id = $order->id;
                $item->product_id = $itemValues["product-id"];
                $item->quantity = $itemValues["quantity"];
                $item->unit_price = $itemValues["unit-price"];
                $item->subtotal = $itemValues["total"];
                $item->discount = 0;
                $item->discount_and_reason = '';
                $item->total = $item->subtotal;

                $item->save();
            }

            //Calculate discounts

            $order->calculateDiscount();

            if ($saved)
            {
                //Return the Json response
                return new OrderResource($order);
            }
        }

        throw new HttpException(400, "Invalid data");
    }
}
