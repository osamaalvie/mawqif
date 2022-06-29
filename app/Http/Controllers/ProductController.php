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
    private const PAGE = 20;
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
        $page = self::PAGE;

        if ($request->has('page')) {
            $page = $request->get('page');
        }

        $products = Product::query()->paginate($page);
        return response()->json(['status' => 'success', 'result' => $products]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|max:255',
            'products.*.price' => 'required',
            'products.*.category' => 'required|max:255',
            'products.*.description' => 'sometimes|required',
            'products.*.avatar' => 'sometimes|required|url'
        ]);

        $products = $request->get('products');
        $data = [];

        $category = Category::query()->get();

        foreach ($products as $product) {

            $category = $category->where('name', $product['category'])->first();

            if ($category) {
                $product['category_id'] = $category->id;
                $product['created_at'] = Carbon::now()->toDateTimeString();
                $product['updated_at'] = Carbon::now()->toDateTimeString();
                unset($product['category']);
                $data[] = $product;
            }
        }

        if (Product::query()->insert($data)) {
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'fail']);
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, Request $request)
    {
        try{

            $product = Product::query()->findOrFail($id);
            $product->delete();
            return response()->json(['status' => 'success']);

        }catch (\Exception $e){
            return response()->json(['status' => 'fail','message' => $e->getMessage()]);
        }


    }

}
