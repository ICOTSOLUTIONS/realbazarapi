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
    public function homePageImage($section)
    {
        if (empty($section)) return response()->json(['status' => false, 'Message' => 'Section not found']);
        $discount = false;
        $featured = false;
        $new_arrival = false;
        $top_rating = false;
        $just_for_you = false;
        if ($section == 'discount') $discount = true;
        if ($section == 'featured') $featured = true;
        if ($section == 'newArrival') $new_arrival = true;
        if ($section == 'topRating') $top_rating = true;
        if ($section == 'justForYou') $just_for_you = true;
        $homePageImage = HomePageImage::where('is_discount', $discount)
            ->where('is_featured', $featured)
            ->where('is_new_arrival', $new_arrival)
            ->where('is_top_rating', $top_rating)
            ->where('is_just_for_you', $just_for_you)
            ->get();
        if (count($homePageImage)) return response()->json(['status' => true, 'Message' => 'HomePageImage found', 'homePageImages' => $homePageImage ?? []], 200);
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
        if ($section == 'discount') $discount = true;
        if ($section == 'featured') $featured = true;
        if ($section == 'newArrival') $new_arrival = true;
        if ($section == 'topRating') $top_rating = true;
        if ($section == 'justForYou') $just_for_you = true;
        $homePageImage = HomePageImage::where('is_discount', $discount)
            ->where('is_featured', $featured)
            ->where('is_new_arrival', $new_arrival)
            ->where('is_top_rating', $top_rating)
            ->where('is_just_for_you', $just_for_you)
            ->get();
        if (count($homePageImage)) return response()->json(['status' => true, 'Message' => 'HomePageImage found', 'homePageImages' => $homePageImage ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'HomePageImage not found']);
    }

    public function addhomePageImage(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'images' => 'required|array',
            'section' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            if (!count($request->images)) throw new Error("Home Page Image Not found!");
            foreach ($request->images as $value) {
                $homePageImage = new HomePageImage();
                if ($request->section == 'discount') $homePageImage->is_discount = true;
                if ($request->section == 'featured') $homePageImage->is_featured = true;
                if ($request->section == 'newArrival') $homePageImage->is_new_arrival = true;
                if ($request->section == 'topRating') $homePageImage->is_top_rating = true;
                if ($request->section == 'justForYou') $homePageImage->is_just_for_you = true;
                $filename = "HomePageImage-" . time() . "-" . rand() . "." . $value->getClientOriginalExtension();
                $value->storeAs('homePageImage', $filename, "public");
                $homePageImage->image = "homePageImage/" . $filename;
                if (!$homePageImage->save()) throw new Error("Home Page Image Not Added!");
            }
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
