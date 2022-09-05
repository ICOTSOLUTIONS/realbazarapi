<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductsResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show()
    {
        $category = Category::has('subCategory')->with('subCategory')->get();
        if (count($category)) return response()->json(['Category' => CategoryResource::collection($category)], 200);
        return response()->json(['Message' => 'Category not found'], 500);
    }

    public function searchCategory(Request $request)
    {
        if (!empty($request->category_id)) {
            if (!empty($request->category_id && $request->subcategory_id)) {
                $product = Product::whereHas('subCategories', function ($query) use ($request) {
                    $query->where('id', $request->subcategory_id);
                    $query->whereRelation('categories', 'id', $request->category_id);
                })->get();
                if (count($product)) {
                    return response()->json(['Product' => ProductsResource::collection($product)], 200);
                } else {
                    return response()->json(['fail' => 'product not found'], 500);
                }
            } else {
                $product = Product::whereHas('subCategories', function ($query) use ($request) {
                    $query->whereRelation('categories', 'id', $request->category_id);
                })->get();
                if (count($product)) {
                    return response()->json(['Product' => ProductsResource::collection($product)], 200);
                } else {
                    return response()->json(['fail' => 'product not found'], 500);
                }
            }
        } else {
            return response()->json(['fail' => 'Parameter is null'], 500);
        }
    }
}
