<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductsResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function show()
    {
        $category = Category::has('subCategory')->with('subCategory')->get();
        if (count($category)) return response()->json(['Category' => CategoryResource::collection($category)], 200);
        return response()->json(['Message' => 'Category not found'], 500);
    }

    public function add(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'category' => 'required|unique:categories,name',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => 'fails', 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $category = new Category();
        $category->name = $request->category;
        $category->url = strtolower(preg_replace('/\s*/', '', $request->category));
        if($category->save()) return response()->json(['Successfull' => 'New Category Added Successfully!'], 200);
        else return response()->json(['Failed' => 'Category not Added!'], 500);
    }

    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'name' => 'required|unique:categories,name,'.$request->id,
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => 'fails', 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $category = Category::where('id',$request->id)->first();
        $category->name = $request->name;
        $category->url = strtolower(preg_replace('/\s*/', '', $request->name));
        if($category->save()) return response()->json(['Successfull' => 'New Category Updated Successfully!'], 200);
        else return response()->json(['Failed' => 'Category not Updated!'], 500);
    }

    public function delete(Request $request)
    {
        $category = Category::where('id',$request->id)->first();
        if(!empty($category)){
            if($category->delete()) return response()->json(['message' => 'Category Deleted'], 200);
            else return response()->json(['message' => 'Category not deleted'], 500);
        }else{
            return response()->json(['message' => 'Category not found'], 500);
        }
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
