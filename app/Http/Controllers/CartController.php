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
        $column = 'session_id';

        if (Auth::check()) {
            $column = 'user_id';
        }

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
        $rules = [
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required',
            'products.*.qty' => 'required',
        ];
        $identifier = [];

        if (Auth::check()) {
            $rules['user_id'] = $id ? '' : 'required';
            $identifier['user_id'] = $id ?: $request->get('user_id');
        } else {
            $rules['session_id'] = $id ? '' : 'required';
            $identifier['session_id'] = $id ?: $request->get('session_id');
        }

        $this->validate($request, $rules);

        $products = $request->get('products');

        $data = [];

        if ($id) {
            Cart::query()->where(key($identifier), $identifier[key($identifier)])->delete();
        }

        foreach ($products as $product) {
            $data[] = [
                key($identifier) => $identifier[key($identifier)],
                'product_id' => $product['product_id'],
                'qty' => $product['qty'],
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ];
        }

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
