<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    public function show()
    {
        $package = Package::all();
        if (count($package)) return response()->json(['Package' => $package??[]], 200);
        return response()->json(['Message' => 'Package not found'], 500);
    }

    public function add(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'name' => 'required|unique:packages,name',
            'date' => 'required|date',
            'expiry_date' => 'required|date',
            'amount' => 'required|numeric',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $package = new Package();
        $package->name = $request->name;
        $package->date = $request->date;
        $package->expiry_date = $request->expiry_date;
        $package->amount = $request->amount;
        if($package->save()) return response()->json(['Message' => 'New Package Added Successfully!'], 200);
        else return response()->json(['Message' => 'Package not Added!'], 500);
    }

    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'name' => 'required|unique:packages,name,'.$request->id,
            'date' => 'required|date',
            'expiry_date' => 'required|date',
            'amount' => 'required|numeric',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $package = Package::where('id',$request->id)->first();
        $package->name = $request->name;
        $package->date = $request->date;
        $package->expiry_date = $request->expiry_date;
        $package->amount = $request->amount;
        if($package->save()) return response()->json(['Message' => 'New Package Updated Successfully!'], 200);
        else return response()->json(['Message' => 'Package not Updated!'], 500);
    }

    public function delete(Request $request)
    {
        $package = Package::where('id',$request->id)->first();
        if(!empty($package)){
            if($package->delete()) return response()->json(['Message' => 'Package Deleted'], 200);
            else return response()->json(['Message' => 'Package not deleted'], 500);
        }else{
            return response()->json(['Message' => 'Package not found'], 500);
        }
    }
}
