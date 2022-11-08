<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackagePayment;
use App\Models\Product;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    public function show()
    {
        $package = Package::where('name', '!=', 'Free')->get();
        if (count($package)) return response()->json(['status' => true, 'Message' => 'Package found', 'Package' => $package ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'Package not found']);
    }

    public function add(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'name' => 'required|unique:packages,name',
            // 'date' => 'required',
            // 'time' => 'required|gt:0',
            'product_qty' => 'required',
            'amount' => 'required|numeric',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $package = new Package();
        $package->name = $request->name;
        $package->date = Carbon::now();
        $package->time = '1';
        $package->period = 'month';
        $package->amount = $request->amount;
        $package->product_qty = $request->product_qty;
        if ($package->save()) return response()->json(['status' => true, 'Message' => 'New Package Added Successfully!', 'package' => $package ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Package not Added!']);
    }

    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required|unique:packages,name,' . $request->id,
            // 'date' => 'required',
            // 'time' => 'required|gt:0',
            // 'period' => 'required',
            'product_qty' => 'required',
            'amount' => 'required|numeric',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $package = Package::where('id', $request->id)->first();
        $package->name = $request->name;
        // $package->date = $request->date;
        // $package->time = $request->time;
        // $package->period = $request->period;
        $package->amount = $request->amount;
        $package->product_qty = $request->product_qty;
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

    public function existPayment(Request $request)
    {
        $exist_user = PackagePayment::where('user_id', auth()->user()->id)
            ->where('package_id', $request->package_id)->first();
        $date = Carbon::now();
        if (!empty($exist_user)) {
            if ($exist_user->end_date > $date) return response()->json(['status' => false, 'Message' => 'You already have active package and your Package expiry date is ' . $exist_user->end_date]);
        }
        return response()->json(['status' => true, 'Message' => 'Please buy new Package']);
    }

    public function payment(Request $request)
    {
        try {
            DB::beginTransaction();
            $date = Carbon::now();
            $existing_package = PackagePayment::where('user_id', auth()->user()->id)
                ->where('package_id', $request->package_id)->first();
            if (!empty($existing_package)) {
                if ($existing_package->end_date > $date) throw new Error('You already have active package and your Package expiry date is ' . $existing_package->end_date);
            }
            $package = Package::where('id', $request->package_id)->first();
            $exist_user = PackagePayment::where('user_id', auth()->user()->id)->first();
            if (!empty($exist_user)) {
                $date = Carbon::now();
                if ($package->period == 'month' || $package->period == 'Month') $end_date = Carbon::now()->addMonths($package->time);
                $exist_user->user_id = auth()->user()->id;
                $exist_user->package_id = $request->package_id;
                $exist_user->start_date = $date;
                $exist_user->end_date = $end_date;
                $exist_user->updated_product_qty = $package->product_qty;
                if (!$exist_user->save()) throw new Error('Package not Buy');
                DB::commit();
                return response()->json(['status' => true, 'Message' => 'Package Buy Successfully'], 200);
            } else {
                $paymentPackage = new PackagePayment();
                if ($package->period == 'month' || $package->period == 'Month') $end_date = Carbon::now()->addMonths($package->time);
                $paymentPackage->user_id = auth()->user()->id;
                $paymentPackage->package_id = $request->package_id;
                $paymentPackage->start_date = $date;
                $paymentPackage->end_date = $end_date;
                $paymentPackage->updated_product_qty = $package->product_qty;
                if (!$paymentPackage->save()) throw new Error('Package not Buy');
                DB::commit();
                return response()->json(['status' => true, 'Message' => 'Package Buy Successfully'], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' =>  $th->getMessage()]);
        }
    }

    public function packageExpiredPeriod(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'skip' => 'required',
            'take' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $skip = $request->skip;
        $take = $request->take;
        $role = $request->role;
        $search = $request->search;
        $expirePeriod = PackagePayment::with(['user.role', 'package']);
        $expirePeriod_count = PackagePayment::with(['user.role', 'package']);
        if (!empty($role)) {
            $expirePeriod->whereHas('user', function ($q) use ($role) {
                $q->whereRelation('role', 'name', $role);
            });
            $expirePeriod_count->whereHas('user', function ($q) use ($role) {
                $q->whereRelation('role', 'name', $role);
            });
        }
        if (!empty($search)) {
            $expirePeriod->where(function ($q) use ($search) {
                $q->where('start_date', 'like', '%' . $search . '%')
                    ->orWhere('end_date', 'like', '%' . $search . '%');
            });
            $expirePeriod_count->where(function ($q) use ($search) {
                $q->where('start_date', 'like', '%' . $search . '%')
                    ->orWhere('end_date', 'like', '%' . $search . '%');
            });
        }
        $expirePeriods = $expirePeriod->skip($skip)->take($take)->get();
        $expirePeriods_counts = $expirePeriod_count->count();
        if (count($expirePeriods)) return response()->json(['status' => true, 'Message' =>  'Expiry Period found', 'expiry' => $expirePeriods ?? [], 'expiryCount' => $expirePeriods_counts ?? []]);
        else return response()->json(['status' => false, 'Message' =>  'Expiry Period not found', 'expiry' => $expirePeriods ?? [], 'expiryCount' => $expirePeriods_counts ?? []]);
    }

    public function subsPackageExpiredPeriod()
    {
        $expirePeriod = PackagePayment::has('user')->with(['user.role', 'package'])->where('user_id', auth()->user()->id)->first();
        $product = Product::has('user')->where('user_id', auth()->user()->id)->count();
        $remaining_product_count = $expirePeriod->updated_product_qty - $product;
        if (!empty($expirePeriod)) return response()->json(['status' => true, 'Message' =>  'Expiry Period found', 'expiry' => $expirePeriod ?? [], 'ProductCount' => $product ?? 0, 'remainProductCount' => $remaining_product_count ?? 0]);
        else return response()->json(['status' => false, 'Message' =>  'Expiry Period not found']);
    }
}
