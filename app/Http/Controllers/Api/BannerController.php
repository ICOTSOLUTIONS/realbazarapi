<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    public function banner($section)
    {
        if (empty($section)) return response()->json(['status' => false, 'Message' => 'Section not found']);
        if($section == 'header') $section = true;
        if($section == 'body') $section = true;
        if($section == 'footer') $section = true;
        $banner = Banner::where('is_header', $section)->orWhere('is_body', $section)->orWhere('is_footer', $section)->get();
        if (count($banner)) return response()->json(['status' => true, 'Message' => 'Banners found', 'banners' => $banner ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'Banners not found']);
    }


    public function addBanner(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'images' => 'required',
            'banner_section' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            if (!count($request->images)) throw new Error("Banner Image Not found!");
            foreach ($request->images as $value) {
                $banner = new Banner();
                if ($request->banner_section == 'header') $banner->is_header = true;
                if ($request->banner_section == 'body') $banner->is_body = true;
                if ($request->banner_section == 'footer') $banner->is_footer = true;
                $filename = "Banner-" . time() . "-" . rand() . "." . $value->getClientOriginalExtension();
                $value->storeAs('banner', $filename, "public");
                $banner->image = "banner/" . $filename;
                $banner->save();
            }
            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Banners Added Successfully'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function updateBanner(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            if (empty($request->images)) throw new Error("Banner Image Not found!");
            $banner = Banner::where('id', $request->id)->first();
            $images = $request->images;
            $filename = "Banner-" . time() . "-" . rand() . "." . $images->getClientOriginalExtension();
            $images->storeAs('banner', $filename, "public");
            $banner->image = "banner/" . $filename;
            if (!$banner->save()) throw new Error("Banner Not added!");
            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Banners Added Successfully'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function deleteBanner(Request $request)
    {
        $banner = Banner::where('id', $request->id)->first();
        if (!empty($banner)) {
            if ($banner->delete()) return response()->json(['status' => true, 'Message' => 'Banner Deleted'], 200);
            else return response()->json(['status' => false, 'Message' => 'Banner not deleted']);
        } else {
            return response()->json(['status' => false, 'Message' => 'Banner not found']);
        }
    }
}
