<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/products/mass', 'ProductController@storeMass');
Route::get('/products/{id}', 'ProductController@show');
Route::post('/orders', 'OrderController@store');
Route::get('/orders/{id}', 'OrderController@show');
