<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductsResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function show()
    {
        $category = Category::has('subCategory')->with('subCategory')->get();
        if (count($category)) return response()->json(['status' => true, 'Message'=> 'Category found', 'Category' => CategoryResource::collection($category)], 200);
        return response()->json(['status' => false, 'Message' => 'Category not found']);
    }

    public function add(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'category' => 'required|unique:categories,name',
            'sub_category' => 'required',
            'category_image' => 'required',
            'subcategory_image' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            if ($request->category && $request->sub_category) {
                $category = new Category();
                $category->name = $request->category;
                $category->url = strtolower(preg_replace('/\s*/', '', $request->category));
                if (!empty($request->category_image)) {
                    $image = $request->category_image;
                    $filename = "Category-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                    $image->storeAs('category', $filename, "public");
                    $category->image = "category/" . $filename;
                }
                if (!$category->save()) throw new Error("New Category not Added!");
                $subcategory = new SubCategory();
                $subcategory->category_id = $category->id;
                $subcategory->name = $request->sub_category;
                $subcategory->url = strtolower(preg_replace('/\s*/', '', $request->category . '/' . $request->sub_category));
                if (!empty($request->subcategory_image)) {
                    $image = $request->subcategory_image;
                    $filename = "SubCategory-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                    $image->storeAs('subcategory', $filename, "public");
                    $subcategory->image = "subcategory/" . $filename;
                }
                if (!$subcategory->save()) throw new Error("New Category not Added!");
                DB::commit();
                $categories = Category::has('subCategory')->with('subCategory')->where('id', $category->id)->get();
                return response()->json(['status' => true, 'Message' => 'New Category Added Successfully!', 'Category' => CategoryResource::collection($categories)], 200);
            } else throw new Error("Category and SubCtegory Required!");
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'name' => 'required|unique:categories,name,' . $request->id,
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $category = Category::where('id', $request->id)->first();
        $category->name = $request->name;
        $category->url = strtolower(preg_replace('/\s*/', '', $request->name));
        if (!empty($request->category_image)) {
            $image = $request->category_image;
            $filename = "Category-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
            $image->storeAs('category', $filename, "public");
            $category->image = "category/" . $filename;
        }
        if ($category->save()) return response()->json(['status' => true, 'Message' => 'New Category Updated Successfully!', 'category' => $category ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Category not Updated!']);
    }

    public function delete(Request $request)
    {
        $category = Category::where('id', $request->id)->first();
        if (!empty($category)) {
            if ($category->delete()) return response()->json(['status' => true, 'Message' => 'Category Deleted'], 200);
            else return response()->json(['status' => false, 'Message' => 'Category not deleted']);
        } else {
            return response()->json(['status' => false, 'Message' => 'Category not found']);
        }
    }

    public function searchCategory(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'category_id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        if (!empty($request->category_id)) {
            if (!empty($request->category_id && $request->subcategory_id)) {
                $product = Product::whereHas('subCategories', function ($query) use ($request) {
                    $query->where('id', $request->subcategory_id);
                    $query->whereRelation('categories', 'id', $request->category_id);
                })->get();
                if (count($product)) {
                    return response()->json(['status' => true, 'Message' => 'Product found', 'Product' => ProductsResource::collection($product)], 200);
                } else {
                    return response()->json(['status' => false, 'Message' => 'Product not found','Product' =>$product??[]]);
                }
            } else {
                $product = Product::whereHas('subCategories', function ($query) use ($request) {
                    $query->whereRelation('categories', 'id', $request->category_id);
                })->get();
                if (count($product)) {
                    return response()->json(['status' => true, 'Message' => 'Product found', 'Product' => ProductsResource::collection($product)], 200);
                } else {
                    return response()->json(['status' => false,  'Message' => 'Product not found','Product' =>$product??[]]);
                }
            }
        } else {
            return response()->json(['status' => false, 'Message' => 'Parameter is null']);
        }
    }
}
