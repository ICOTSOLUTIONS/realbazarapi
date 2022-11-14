<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\DemandProduct;
use App\Models\DemandProductImage;
use App\Models\User;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\NotiSend;
use Illuminate\Support\Facades\Validator;

class DemandProductController extends Controller
{
    public function demandProduct()
    {
        $demand = DemandProduct::with('demand_image')->get();
        if (count($demand)) return response()->json(['status' => true, 'Message' => 'Demand Products found', 'demand' => $demand ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Demand Product not found', 'demand' => $demand ?? []]);
    }

    public function addDemandProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'name' => 'required',
            'detail' => 'required',
            'qty' => 'required',
            'images' => 'required|array',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $demand = new DemandProduct();
            $demand->name = $request->name;
            $demand->detail = $request->detail;
            $demand->qty = $request->qty;
            $demand->timer = date('Y-m-d H:i:s', strtotime(Carbon::now()));
            if (!$demand->save()) throw new Error('Demand Product not save');
            if (!count($request->images)) throw new Error("Image Not found!");
            foreach ($request->images as $value) {
                $demandImages = new DemandProductImage();
                $demandImages->demand_product_id = $demand->id;
                $filename = "DemandImages-" . time() . "-" . rand() . "." . $value->getClientOriginalExtension();
                $value->storeAs('DemandImages', $filename, "public");
                $demandImages->images = "demandImages/" . $filename;
                if (!$demandImages->save()) throw new Error("Home Page Image Not Added!");
            }
            $users = User::whereHas('role', function ($query) {
                $query->where('name', 'wholesaler')->orWhere('name', 'retailer');
            })->get();
            if (!count($users)) return response()->json(['status' => false, 'Message' => "Users not found"]);
            $title = 'DEMAND PRODUCTS';
            $message = 'New Request for Demand Product';
            foreach ($users as  $user) {
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, $request->title, $request->message);
            }
            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Demand Product Request Successfully'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }
}
