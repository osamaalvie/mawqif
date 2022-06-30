<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductController extends Controller
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $page = PAGE_SIZE;

        if ($request->has('page')) {
            $page = $request->get('page');
        }

        $products = Product::query()->simplePaginate($page);
        return response()->json(['status' => 'success', 'result' => $products]);
    }

    /**
     * fetch single product details
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function details($id, Request $request){
        try{
            $product = Product::query()->findOrFail($id);

            return response()->json(['status' => 'success', 'result' => $product]);
        }catch (\Exception $e){
            return response()->json(['status' => 'success', 'message' => $e->getMessage()]);
        }
    }

    /**
     * store products
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        //validate every product
        $this->validate($request, [
            'products' => 'required|array|min:1|max:' . MAX_INSERT,
            'products.*.name' => 'required|max:255|unique:products',
            'products.*.price' => 'required',
            'products.*.category' => 'required|max:255',
            'products.*.description' => 'sometimes|required',
            'products.*.avatar' => 'sometimes|required|url'
        ]);

        $products = $request->get('products');

        $data = [];

        //fetch all categories
        $category = Category::query()->get();

        //loop through every product from request
        foreach ($products as $product) {

            //check if category exists
            $category = $category->where('name', $product['category'])->first();

            //category exists so create data for bulk insert
            if ($category) {
                $product['category_id'] = $category->id;
                $product['created_at'] = Carbon::now()->toDateTimeString();
                $product['updated_at'] = Carbon::now()->toDateTimeString();
                unset($product['category']);
                $data[] = $product;
            }
        }

        //if bulk insert successfully
        if (Product::query()->insert($data)) {
            return response()->json(['status' => 'success','message' => 'products inserted successfully']);
        } else {
            //send error message
            return response()->json(['status' => 'fail','message' => 'error while inserting products']);
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, Request $request)
    {
        try {

            //check if product exist otherwise catch exception
            $product = Product::query()->findOrFail($id);
            //delete if found
            $product->delete();
            return response()->json(['status' => 'success','message' => 'Product deleted successfully']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }


    }

}
