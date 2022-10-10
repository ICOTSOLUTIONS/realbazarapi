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
        if (count($package)) return response()->json(['status' => true, 'Package' => $package ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'Package not found']);
    }

    public function add(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'name' => 'required|unique:packages,name',
            'date' => 'required',
            'expiry_date' => 'required',
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
        if ($package->save()) return response()->json(['status' => true, 'Message' => 'New Package Added Successfully!'], 200);
        else return response()->json(['status' => false, 'Message' => 'Package not Added!']);
    }

    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required|unique:packages,name,' . $request->id,
            'date' => 'required',
            'expiry_date' => 'required',
            'amount' => 'required|numeric',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $package = Package::where('id', $request->id)->first();
        $package->name = $request->name;
        $package->date = $request->date;
        $package->expiry_date = $request->expiry_date;
        $package->amount = $request->amount;
        if ($package->save()) return response()->json(['status' => true, 'Message' => 'Package Updated Successfully!'], 200);
        else return response()->json(['status' => false, 'Message' => 'Package not Updated!']);
    }

    public function delete(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $package = Package::where('id', $request->id)->first();
        if (!empty($package)) {
            if ($package->delete()) return response()->json(['status' => true, 'Message' => 'Package Deleted'], 200);
            else return response()->json(['status' => false, 'Message' => 'Package not deleted']);
        } else {
            return response()->json(['status' => false, 'Message' => 'Package not found']);
        }
    }
}
