<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SocialLoginController extends Controller
{
    protected function googleLogin()
    {
        $validation = Validator::make(
            request()->all(),
            [
                'id' => 'required',
                'role' => 'required',
            ]
        );

        if ($validation->fails()) {
            return response(['status' => false, 'Message' => $validation->errors()]);
        }

        $existingAccount = User::where('account_type', 'google')->where('email', request()->email)->where('role_id', 2)->first();
        if ($existingAccount != null) {
            if (auth()->loginUsingId($existingAccount->id)) {
                $user = auth()->user();
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                $user->save();
                return response()->json(["status" => true, "Message" => 'User Sign In Success', 'user' => $user, 'token' => $token], 200);
            } else {
                return response()->json(['status' => false, "Message" => 'User Sign In failed!']);
            }
        }

        $socailLogin = new User();
        $socailLogin->role_id =  2;
        $socailLogin->account_type =  'google';
        $socailLogin->email =  request()->email;
        $socailLogin->account_id =  request()->id;
        $socailLogin->family_name =  request()->family_name;
        $socailLogin->given_name =  request()->given_name;
        $socailLogin->first_name =  request()->first_name;
        $socailLogin->last_name =  request()->last_name;
        if (isset(request()->phone)) $socailLogin->phone =  request()->phone;
        if (isset(request()->photo)) $socailLogin->image =  request()->photo;
        if ($socailLogin->save()) {
            if (auth()->loginUsingId($socailLogin->id)) {
                $user = auth()->user();
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                $user->save();
                return response()->json(["status" => true, "Message" => 'User Sign In Success', 'user' => $user, 'token' => $token], 200);
            } else {
                return response()->json(['status' => false, "Message" => 'User Sign In failed!'], 500);
            }
        }
    }

    protected function facebookLogin()
    {
        $validation = Validator::make(
            request()->all(),
            [
                'id' => 'required',
                'role' => 'required',
            ]
        );

        if ($validation->fails()) {
            return response(['status' => false, 'message' => $validation->errors()], 500);
        }

        $existingAccount = User::where('account_type', 'facebook')->where('email', request()->email)->where('role_id', 2)->first();
        if ($existingAccount != null) {
            if (auth()->loginUsingId($existingAccount->id)) {
                $user = auth()->user();
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                $user->save();
                return response()->json(["status" => 'Success', "message" => 'User Sign In Success', 'user' => $user, 'token' => $token], 200);
            } else {
                return response()->json(["message" => 'User Sign In failed!'], 500);
            }
        }

        $socailLogin = new User();
        $socailLogin->role_id =  2;
        $socailLogin->account_type =  'facebook';
        $socailLogin->email =  request()->email;
        $socailLogin->account_id =  request()->id;
        $socailLogin->family_name =  request()->family_name;
        $socailLogin->given_name =  request()->given_name;
        $socailLogin->first_name =  request()->first_name;
        $socailLogin->last_name =  request()->last_name;
        if (isset(request()->photo)) $socailLogin->image =  request()->photo;
        if (isset(request()->phone)) $socailLogin->phone =  request()->phone;
        if ($socailLogin->save()) {
            if (auth()->loginUsingId($socailLogin->id)) {
                $user = auth()->user();
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                $user->save();
                return response()->json(["status" => 'Success', "message" => 'User Sign In Success', 'user' => $user, 'token' => $token], 200);
            } else {
                return response()->json(["message" => 'User Sign In failed!'], 500);
            }
        }
    }
}
