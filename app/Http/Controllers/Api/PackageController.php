<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackagePayment;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    public function show()
    {
        $package = Package::all();
        if (count($package)) return response()->json(['status' => true, 'Message' => 'Package found', 'Package' => $package ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'Package not found']);
    }

    public function add(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'name' => 'required|unique:packages,name',
            // 'date' => 'required',
            'time' => 'required|gt:0',
            'period' => 'required',
            'amount' => 'required|numeric',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $package = new Package();
        $package->name = $request->name;
        $package->date = Carbon::now();
        $package->time = $request->time;
        $package->period = $request->period;
        $package->amount = $request->amount;
        if ($package->save()) return response()->json(['status' => true, 'Message' => 'New Package Added Successfully!','package'=>$package??[]], 200);
        else return response()->json(['status' => false, 'Message' => 'Package not Added!']);
    }

    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required|unique:packages,name,' . $request->id,
            // 'date' => 'required',
            'time' => 'required|gt:0',
            'period' => 'required',
            'amount' => 'required|numeric',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $package = Package::where('id', $request->id)->first();
        $package->name = $request->name;
        // $package->date = $request->date;
        $package->time = $request->time;
        $package->period = $request->period;
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

    public function payment(Request $request)
    {
        try {
            DB::beginTransaction();
            $package = Package::where('id', $request->package_id)->first();
            $exist_user = PackagePayment::where('user_id', auth()->user()->id)
                ->where('package_id', $request->package_id)->first();
            if (!empty($exist_user)) {
                $date = Carbon::now();
                if ($exist_user->end_date > $date) throw new Error('Your Package expiry date is ' . $exist_user->end_date);
                else {
                    $response = 'success';
                    if (!empty($response)) {
                        if ($package->period == 'year') $end_date = Carbon::now()->addYear($package->time);
                        if ($package->period == 'month') $end_date = Carbon::now()->addMonths($package->time);
                        if ($package->period == 'week') $end_date = Carbon::now()->addDays($package->time * 7);
                        $exist_user->user_id = auth()->user()->id;
                        $exist_user->package_id = $request->package_id;
                        $exist_user->start_date = $date;
                        $exist_user->end_date = $end_date;
                        if (!$exist_user->save()) throw new Error('Package not Buy');
                        DB::commit();
                        return response()->json(['status' => true, 'Message' => 'Package Buy Successfully'], 200);
                    } else throw new Error('Response not success');
                }
            } else {
                $response = 'success';
                if (!empty($response)) {
                    $date = Carbon::now();
                    $paymentPackage = new PackagePayment();
                    if ($package->period == 'year' || $package->period == 'Year' ) $end_date = Carbon::now()->addYear($package->time);
                    if ($package->period == 'month' || $package->period == 'Month') $end_date = Carbon::now()->addMonths($package->time);
                    if ($package->period == 'week' || $package->period == 'Week') $end_date = Carbon::now()->addDays($package->time * 7);
                    $paymentPackage->user_id = auth()->user()->id;
                    $paymentPackage->package_id = $request->package_id;
                    $paymentPackage->start_date = $date;
                    $paymentPackage->end_date = $end_date;
                    if (!$paymentPackage->save()) throw new Error('Package not Buy');
                    DB::commit();
                    return response()->json(['status' => true, 'Message' => 'Package Buy Successfully'], 200);
                } else throw new Error('Response not success');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' =>  $th->getMessage()]);
        }
    }

    public function packageExpiredPeriod()
    {
        $expirePeriod = PackagePayment::with(['user', 'package'])->get();
        if (count($expirePeriod)) return response()->json(['status' => true, 'Message' =>  'Expiry Period found', 'expiry' => $expirePeriod ?? [],'duration'=>'877']);
        else return response()->json(['status' => false, 'Message' =>  'Expiry Period not found']);
    }
}
