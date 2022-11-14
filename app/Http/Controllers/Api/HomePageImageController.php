<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomePageImage;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomePageImageController extends Controller
{
    public function homePageImage($section, $role = null)
    {
        if (empty($section)) return response()->json(['status' => false, 'Message' => 'Section not found']);
        $discount = false;
        $featured = false;
        $new_arrival = false;
        $top_rating = false;
        $just_for_you = false;
        $trending = false;
        if ($section == 'discount') $discount = true;
        if ($section == 'featured') $featured = true;
        if ($section == 'newArrival') $new_arrival = true;
        if ($section == 'topRating') $top_rating = true;
        if ($section == 'justForYou') $just_for_you = true;
        if ($section == 'trending') $trending = true;
        $homePageImage = HomePageImage::query();

        $homePageImage->where('is_discount', $discount)
            ->where('is_featured', $featured)
            ->where('is_new_arrival', $new_arrival)
            ->where('is_top_rating', $top_rating)
            ->where('is_just_for_you', $just_for_you)
            ->where('is_trending', $trending);
        if ($role == 'retailer') {
            $homePageImage->where('is_retailer', true);
        }
        if ($role == 'wholesaler') {
            $homePageImage->where('is_wholesaler', true);
        }
        $homePageImages = $homePageImage->orderBy('id', 'DESC')->get();
        if (count($homePageImages)) return response()->json(['status' => true, 'Message' => 'HomePageImage found', 'homePageImages' => $homePageImages ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'HomePageImage not found']);
    }

    public function homePageImages($section)
    {
        if (empty($section)) return response()->json(['status' => false, 'Message' => 'Section not found']);
        $discount = false;
        $featured = false;
        $new_arrival = false;
        $top_rating = false;
        $just_for_you = false;
        $trending = false;
        if ($section == 'discount') $discount = true;
        if ($section == 'featured') $featured = true;
        if ($section == 'newArrival') $new_arrival = true;
        if ($section == 'topRating') $top_rating = true;
        if ($section == 'justForYou') $just_for_you = true;
        if ($section == 'trending') $trending = true;
        $homePageImage = HomePageImage::where('is_discount', $discount)
            ->where('is_featured', $featured)
            ->where('is_new_arrival', $new_arrival)
            ->where('is_top_rating', $top_rating)
            ->where('is_just_for_you', $just_for_you)
            ->where('is_trending', $trending)
            ->get();
        if (count($homePageImage)) return response()->json(['status' => true, 'Message' => 'HomePageImage found', 'homePageImages' => $homePageImage ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'HomePageImage not found']);
    }

    public function addhomePageImage(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'images' => 'required',
            'section' => 'required',
            'role' => 'required',
            'url' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            if (empty($request->images)) throw new Error("Home Page Image Not found!");
            // foreach ($request->images as $value) {
                $value = $request->images;
                $homePageImage = new HomePageImage();
                // $homePageImage->title = $request->title ?? '';
                $homePageImage->url = $request->url ?? '';
                if ($request->section == 'discount') $homePageImage->is_discount = true;
                if ($request->section == 'featured') $homePageImage->is_featured = true;
                if ($request->section == 'newArrival') $homePageImage->is_new_arrival = true;
                if ($request->section == 'topRating') $homePageImage->is_top_rating = true;
                if ($request->section == 'justForYou') $homePageImage->is_just_for_you = true;
                if ($request->section == 'trending') $homePageImage->is_trending = true;
                if ($request->role == 'retailer') $homePageImage->is_retailer = true;
                if ($request->role == 'wholesaler') $homePageImage->is_wholesaler = true;
                $filename = "HomePageImage-" . time() . "-" . rand() . "." . $value->getClientOriginalExtension();
                $value->storeAs('homePageImage', $filename, "public");
                $homePageImage->image = "homePageImage/" . $filename;
                if (!$homePageImage->save()) throw new Error("Home Page Image Not Added!");
            // }
            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Home Page Image Added Successfully'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function updatehomePageImage(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            if (!empty($request->images)) {
                $homePageImage = HomePageImage::where('id', $request->id)->first();
                $homePageImage->url = $request->url ?? '';
                $images = $request->images;
                $filename = "homePageImage-" . time() . "-" . rand() . "." . $images->getClientOriginalExtension();
                $images->storeAs('homePageImage', $filename, "public");
                $homePageImage->image = "homePageImage/" . $filename;
                if (!$homePageImage->save()) throw new Error("Home Page Image Not Updated!");
            }
            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Home Page Image Updated Successfully'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function deletehomePageImage(Request $request)
    {
        $homePageImage = HomePageImage::where('id', $request->id)->first();
        if (!empty($homePageImage)) {
            if ($homePageImage->delete()) return response()->json(['status' => true, 'Message' => 'Home Page Image Deleted'], 200);
            else return response()->json(['status' => false, 'Message' => 'Home Page Image not deleted']);
        } else {
            return response()->json(['status' => false, 'Message' => 'Home Page Image not found']);
        }
    }
}
