<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductController extends Controller {

    /**
     * @param $id
     * @return ProductResource
     */
    public function show($id)
    {
        return new ProductResource(Product::find($id));
    }

    /**
     * @param Request $request
     * @return ProductResource
     */
    public function storeMass(Request $request)
    {

        $products = $request->all();

        try
        {
            foreach ($products as $productValues)
            {
                // Check for update
                $product = Product::where('product_id', $productValues['id'])->first();

                if (is_null($product))
                {
                    // no update
                    $product = new Product;
                }
                $product->product_id = $productValues['id'];
                $product->description = $productValues['description'];
                $product->category = $productValues['category'];
                $product->price = $productValues['price'];

                $product->save();
            }

            return response()->json([
                'message' => 'Product Bulk insert successful!'
            ]);
        } catch (\Exception $e)
        {
            throw new HttpException(400, $e->getMessage());
        }
    }


    /**
     * @param Request $request
     * @return ProductResource
     */
    public function store(Request $request)
    {

        $product = new Product;
        $product->product_id = $request->input('id');
        $product->description = $request->input('description');
        $product->category = $request->input('category');
        $product->price = $request->input('price');

        $id = $product->save();

        if ($id)
        {
            return new ProductResource(Product::find($id));
        }
        throw new HttpException(400, "Invalid data");
    }

}
