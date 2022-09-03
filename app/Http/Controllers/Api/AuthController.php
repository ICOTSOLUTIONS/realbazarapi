<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'username' => 'required|unique:users,username',
            'phone' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 'fails', 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $user = new User();
            $user->role_id = 2;
            $user->username =  $request->username;
            $user->first_name =  $request->first_name;
            $user->last_name =  $request->last_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            if ($user->save()) {
                DB::commit();
                $client = User::with('role')->where('id', $user->id)->first();
                return response()->json(['message' => "User Successfully Added", 'user' => $client,], 200);
            } else throw new Error("User Not Added!");
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => "User not Added"], 500);
        }
    }

    public function login(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => 'fails', 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        if (auth()->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ])) {
            $user = auth()->user();
            if ($user->is_active == true) {
                $token = $user->createToken('token')->accessToken;
                return response()->json(['token' => $token, 'user' => $user], 200);
            } else {
                auth()->logout();
                return response()->json(['message' => 'Admin Approval required'], 500);
            }
        } else {
            return response()->json(['error' => 'Invalid Credentials'], 500);
        }
    }

    public function forgot(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 'fails', 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $user = User::where('email', $request->email)->first();
        if (!empty($user)) {
            $user->token = rand(1, 10000);
            $user->save();
            $email = $request->email;
            $token = $user->token;
            Mail::send('admin.mail.appForgotPassword',  compact('email', 'token'), function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Reset Password');
            });
            return response()->json(['message' => "Reset Email send to {$email}", 'token' => $token, 'user' => $user,], 200);
        } else {
            return response()->json(['message' => "User not found"], 500);
        }
    }

    public function reset(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'email' => 'required',
            'token' => 'required',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 'fails', 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $user = User::where('email', $request->email)->where('token', $request->token)->first();
        if (empty($user)) return response()->json(['message' => "User not found"], 500);
        if (Hash::check($request->password, $user->password)) return response()->json(['message', 'Please use different from current password.'], 500);
        $user->password = Hash::make($request->password);
        $user->token = null;
        $user->save();
        return response()->json(['message' => "Password reset succesfully", 'user' => $user,], 200);
    }


    public function edit_profile()
    {
        $user = User::with('role')->where('id', auth()->user()->id)->first();
        if (!empty($user)) return response()->json(['user', $user ?? []], 200);
        else return response()->json(['message', 'user not found'], 500);
    }

    public function update_profile(Request $request)
    {
        $user = User::where('id', auth()->user()->id)->first();
        if (!empty($user)) {
            $valid = Validator::make($request->all(), [
                'email' => 'required|email|unique:users,email,' . auth()->user()->id,
                'username' => 'required|unique:users,username,' . auth()->user()->id,
                'phone' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
            ]);
            if ($valid->fails()) {
                return response()->json(['status' => 'fails', 'message' => 'Validation errors', 'errors' => $valid->errors()]);
            }
            try {
                DB::beginTransaction();
                $user->email = $request->email;
                $user->username = $request->username;
                $user->first_name = $request->fname;
                $user->last_name = $request->lname;
                $user->phone = $request->phone;
                if (!empty($request->image)) {
                    $image = $request->image;
                    $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                    $image->storeAs('image', $filename, "public");
                    $user->image = "image/" . $filename;
                }
                if ($user->save()) {
                    DB::commit();
                    $user = User::with('role')->where('id', $user->id)->get();
                    return response()->json(['message' => "User Successfully Updated", 'user' => $user,], 200);
                } else throw new Error("User Not Updated");
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json(['message' => "User not Update"], 500);
            }
        } else return response()->json(['message', 'User not found'], 500);
    }
}
