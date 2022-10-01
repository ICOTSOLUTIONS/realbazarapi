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
            'sub_category' => 'required',
            'subcategory_image' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation errors', 'errors' => $valid->errors()],500);
        }
        $category = Category::where('id', $request->category_id)->first();
        $subcategory = SubCategory::whereHas('categories',function ($query) use($category) {
            $query->where('id',$category->id);
           })->where('name', $request->sub_category)->first();
        if(!is_object($subcategory)){
            $subcategory = new SubCategory();
            $subcategory->category_id = $category->id;
            $subcategory->name = $request->sub_category;
            $subcategory->url = strtolower(preg_replace('/\s*/', '', $category->name.'/'.$request->sub_category));
            if (!empty($request->subcategory_image)) {
                $image = $request->subcategory_image;
                $filename = "SubCategory-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('subcategory', $filename, "public");
                $subcategory->image = "subcategory/" . $filename;
            }
            if (!$subcategory->save()) return response()->json(['Failed' => 'Sub Category not Added!'], 500);
            $subcategories = SubCategory::has('categories')->with('categories')->where('id',$subcategory->id)->get();
            return response()->json(['Successfull' => 'New Sub Category Added Successfully!', 'SubCategory' => SubCategoryResource::collection($subcategories)], 200);
        } else return response()->json(['Failed' => 'Sub Category already exist!'], 500);
    }

    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'category_id' => 'required',
            'subcategory' => 'required|unique:categories,name,' . $request->id,
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation errors', 'errors' => $valid->errors()],500);
        }
        $category = Category::where('id', $request->category_id)->first();
        $subcategory = SubCategory::where('id',$request->id)->first();
        if(empty($sub_category)) return response()->json(['Successfull' => 'Sub Category not found'], 500);
        $subcategory->category_id = $category->id;
        $subcategory->name = $request->subcategory;
        $subcategory->url = strtolower(preg_replace('/\s*/', '', $category->name . '/' . $request->subcategory));
        if (!empty($request->subcategory_image)) {
            $image = $request->subcategory_image;
            $filename = "SubCategory-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
            $image->storeAs('subcategory', $filename, "public");
            $subcategory->image = "subcategory/" . $filename;
        }
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
