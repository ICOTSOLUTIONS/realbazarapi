<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubCategoryResource;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    public function show()
    {
        $subcategory = SubCategory::has('categories')->with('categories')->get();
        if (count($subcategory)) return response()->json(['SubCategory' => SubCategoryResource::collection($subcategory)], 200);
        return response()->json(['Message' => 'SubCategory not found'], 500);
    }

    public function fetchSubCategory($id)
    {
        if(empty($id)) return response()->json(['status' => false, 'message' => 'Id not found'],500);
        $subcategory = SubCategory::has('categories')->with('categories')->where('category_id',$id)->get();
        if (count($subcategory)) return response()->json(['SubCategory' => SubCategoryResource::collection($subcategory)], 200);
        return response()->json(['Message' => 'SubCategory not found'], 500);
    }

    public function add(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'category_id' => 'required',
            'subcategory' => 'required|unique:categories,name',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => 'fails', 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $category = Category::where('id', $request->category_id)->first();
        $subcategory = new SubCategory();
        $subcategory->category_id = $category->id;
        $subcategory->name = $request->subcategory;
        $subcategory->url = strtolower(preg_replace('/\s*/', '', $category->name . '/' . $request->subcategory));
        if ($subcategory->save()) return response()->json(['Successfull' => 'New Sub Category Added Successfully!', 'subcategory' => $subcategory ?? []], 200);
        else return response()->json(['Failed' => 'Category not Added!'], 500);
    }

    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'category_id' => 'required',
            'subcategory' => 'required|unique:categories,name,' . $request->id,
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => 'fails', 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $category = Category::where('id', $request->category_id)->first();
        $subcategory = SubCategory::where('id',$request->id)->first();
        if(empty($sub_category)) return response()->json(['Successfull' => 'Sub Category not found'], 500);
        $subcategory->category_id = $category->id;
        $subcategory->name = $request->subcategory;
        $subcategory->url = strtolower(preg_replace('/\s*/', '', $category->name . '/' . $request->subcategory));
        if ($subcategory->save()) return response()->json(['Successfull' => 'Sub Category Updated Successfully!', 'subcategory' => $subcategory ?? []], 200);
        else return response()->json(['Failed' => 'Category not Updated!'], 500);
    }

    public function delete(Request $request)
    {
        $sub_category = SubCategory::where('id', $request->id)->first();
        if (!empty($sub_category)) {
            if ($sub_category->delete()) return response()->json(['message' => 'Sub Category Deleted'], 200);
            else return response()->json(['message' => 'Sub Category not deleted'], 500);
        } else {
            return response()->json(['message' => 'Sub Category not found'], 500);
        }
    }

}
