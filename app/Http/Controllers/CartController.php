<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Get Cart by user_id for authenticated user or session_id for guest user
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($id, Request $request)
    {
        $column = 'session_id'; //for dynamic column session_id in case of Guest user or user_id in case of Autheticated user

        //check if user is authenticated
        if (Auth::check()) {
            $column = 'user_id';
        }

        //fetch cart details
        $cart = Cart::query()->where($column, $id)->get();

        return response()->json(['status' => 'success', 'result' => $cart]);
    }

    /**
     * common function for store and update cart
     * @param Request $request
     * @param $isUpdate
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function addAndUpdateCart(Request $request, $id = null)
    {
        //rules for validation
        $rules = [
            'products' => 'required|array|min:1', //required and minimum one product
            'products.*.product_id' => 'required|integer', //required
            'products.*.qty' => 'required|integer', //required
        ];
        $identifier = []; //for dynamic column session_id in case of Guest user or user_id in case of Autheticated user

        //check if user is authenticated
        if (Auth::check()) {
            $identifier['user_id'] = Auth::user()->id;
        } else {
            $rules['session_id'] = $id ? '' : 'required'; //$id wil exists in case of update
            $identifier['session_id'] = $id ?: $request->get('session_id');
        }

        $this->validate($request, $rules);

        $products = $request->get('products'); //fetch all products from request

        $data = [];

        if ($id) { //if id exists in case of update delete the cart first
            Cart::query()->where(key($identifier), $identifier[key($identifier)])->delete();
        }

        //prepare bulk data for cart
        foreach ($products as $product) {
            $data[] = [
                key($identifier) => $identifier[key($identifier)],
                'product_id' => $product['product_id'],
                'qty' => $product['qty'],
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ];
        }

        //insert data in carts table
        if (Cart::query()->insert($data)) {
            $message = $id ? 'updated' : 'inserted';
            return response()->json(['status' => 'success', 'message' => "Cart is $message successfully"]);
        } else {
            return response()->json(['status' => 'fail']);
        }
    }


    /**
     * create new cart
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        return $this->addAndUpdateCart($request);
    }

    /**
     * update cart
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update($id, Request $request)
    {
        return $this->addAndUpdateCart($request, $id);
    }

    /**
     * delete an existing cart by session id or user id
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, Request $request)
    {
        try {

            //check if user is authenticated user
            if (Auth::check()) {
                $identifier['user_id'] = $id;
            } else {
                $identifier['session_id'] = $id;
            }

            //delete cart by session id or user id
            Cart::query()->where(key($identifier), $id)->delete();

            return response()->json(['status' => 'success', 'message' => 'Cart is deleted successfully']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }
}
