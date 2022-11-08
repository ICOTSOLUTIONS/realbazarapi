<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
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
        $role = Role::where('name', request()->role)->first();
        if (empty($role)) return response()->json(['status' => false, 'Message' => 'Role not found']);
        $existingAccount = User::where('account_type', 'google')->where('email', request()->email)->where('role_id', $role->id)->first();
        if ($existingAccount != null) {
            if (auth()->loginUsingId($existingAccount->id)) {
                $user = auth()->user()->load('role');
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                $user->save();
                return response()->json(["status" => true, "Message" => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            } else {
                return response()->json(['status' => false, "Message" => 'Invalid Credentials!']);
            }
        }

        $socailLogin = new User();
        $socailLogin->role_id =  $role->id;
        $socailLogin->account_type =  'google';
        $socailLogin->email =  request()->email;
        $socailLogin->account_id =  request()->id;
        $socailLogin->family_name =  request()->familyName;
        $socailLogin->given_name =  request()->givenName;
        $socailLogin->first_name =  request()->first_name;
        $socailLogin->last_name =  request()->last_name;
        $socailLogin->username =  request()->name;
        if (isset(request()->phone)) $socailLogin->phone =  request()->phone;
        if (isset(request()->photo)) $socailLogin->image =  request()->photo;
        if ($socailLogin->save()) {
            if (auth()->loginUsingId($socailLogin->id)) {
                $user = auth()->user()->load('role');
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                $user->save();
                return response()->json(["status" => true, "Message" => 'Login Successfull',  'token' => $token, 'user' => $user], 200);
            } else {
                return response()->json(['status' => false, "Message" => 'Invalid Credentials!']);
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
            return response(['status' => false, 'Message' => $validation->errors()]);
        }
        $role = Role::where('name', request()->role)->first();
        if (empty($role)) return response()->json(['status' => false, 'Message' => 'Role not found']);
        $existingAccount = User::where('account_type', 'facebook')->where('email', request()->email)->where('role_id', $role->id)->first();
        if ($existingAccount != null) {
            if (auth()->loginUsingId($existingAccount->id)) {
                $user = auth()->user()->load('role');
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                $user->save();
                return response()->json(["status" => true, "Message" => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            } else {
                return response()->json(['status' => false, "Message" => 'Invalid Credentials!']);
            }
        }

        $socailLogin = new User();
        $socailLogin->role_id =  $role->id;
        $socailLogin->account_type =  'facebook';
        $socailLogin->email =  request()->email;
        $socailLogin->account_id =  request()->id;
        $socailLogin->family_name =  request()->familyName;
        $socailLogin->given_name =  request()->givenName;
        $socailLogin->first_name =  request()->first_name;
        $socailLogin->last_name =  request()->last_name;
        $socailLogin->username =  request()->name;
        if (isset(request()->photo)) $socailLogin->image =  request()->photo;
        if (isset(request()->phone)) $socailLogin->phone =  request()->phone;
        if ($socailLogin->save()) {
            if (auth()->loginUsingId($socailLogin->id)) {
                $user = auth()->user()->load('role');
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                $user->save();
                return response()->json(["status" => true, "message" => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            } else {
                return response()->json(['status' => false, "Message" => 'Invalid Credentials!']);
            }
        }
    }
}
