<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReferralUser;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReferralController extends Controller
{
    public function referralUsers()
    {
        $referral_users = ReferralUser::all();
        if (count($referral_users)) return response()->json(['status' => true, 'Message' => 'Referral User found', 'referral_users' => $referral_users ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'Referral User not found', 'referral_users' => $referral_users ?? []]);
    }

    public function showReferralUsers($id)
    {
        if (empty($id)) return response()->json(['status' => false, 'Message' => 'ID not found']);
        $referral_users = ReferralUser::where('id', $id)->first();
        if (!empty($referral_users)) return response()->json(['status' => true, 'Message' => 'Referral User found', 'referral_users' => $referral_users ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'Referral User not found', 'referral_users' => $referral_users ?? []]);
    }

    public function addReferralUsers(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:referral_users,email',
            'phone' => 'required|digits:11|unique:referral_users,phone',
            'cnic' => 'required|digits:13|unique:referral_users,cnic',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $referr_code = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8);
            $referral_users = new ReferralUser();
            $referral_users->name = $request->name;
            $referral_users->email = $request->email;
            $referral_users->phone = $request->phone;
            $referral_users->cnic = $request->cnic;
            $referral_users->referral_code = $referr_code;
            if (!$referral_users->save()) throw new Error("Referral User Not added!");
            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Referral User Added Successfully'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function updateReferralUsers(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:referral_users,email,' . auth()->user()->id,
            'phone' => 'required|digits:11|unique:referral_users,phone,' . auth()->user()->id,
            'cnic' => 'required|digits:13|unique:referral_users,cnic,' . auth()->user()->id,
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $referral_users = ReferralUser::where('id', auth()->user()->id)->first();
            if (empty($referral_users)) throw new Error('Referral User not found');
            $referral_users->name = $request->name;
            $referral_users->email = $request->email;
            $referral_users->phone = $request->phone;
            $referral_users->cnic = $request->cnic;
            if (!$referral_users->save()) throw new Error("Referral User Not added!");
            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Referral User Added Successfully'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function deleteReferralUsers()
    {
        if (empty($id)) return response()->json(['status' => false, 'Message' => 'ID not found']);
        $referral_users = ReferralUser::where('id', $id)->first();
        if (!empty($referral_users)) {
            if ($referral_users->delete()) return response()->json(['status' => true, 'Message' => 'Referral User Delete Successfully'], 200);
            else return response()->json(['status' => false, 'Message' => 'Referral User not Delete']);
        } else return response()->json(['status' => false, 'Message' => 'Referral User not found', 'referral_users' => $referral_users ?? []]);
    }
}
