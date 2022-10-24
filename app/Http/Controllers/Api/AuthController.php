<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FollowUserShop;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function wholesaler()
    {
        $wholesalers = User::with('role')->where('role_id', 4)->get();
        if (count($wholesalers)) return response()->json(['status' => true, 'wholesalers' => $wholesalers ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'not found']);
    }

    public function user()
    {
        $users = User::with('role')->where('role_id', 3)->get();
        if (count($users)) return response()->json(['status' => true, 'users' => $users ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'not found']);
    }

    public function retailer()
    {
        $retailers = User::with('role')->where('role_id', 5)->get();
        if (count($retailers)) return response()->json(['status' => true, 'retailers' => $retailers ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'not found']);
    }

    public function signup(Request $request)
    {
        $rules = [
            'role' => 'required',
            'name' => 'required',
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'password' => 'required',
        ];
        if (is_numeric($request->get('emailphone'))) {
            $rules['emailphone'] = 'required|digits:11|unique:users,phone';
        } else {
            $rules['emailphone'] = 'required|email|unique:users,email';
        }
        $valid = Validator::make($request->all(), $rules);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $user = new User();
            if ($request->role == 'user') $user->role_id = 3;
            if ($request->role == 'wholesaler') $user->role_id = 4;
            if ($request->role == 'retailer') $user->role_id = 5;
            $user->username =  $request->name;
            $user->first_name =  $request->first_name;
            $user->last_name =  $request->last_name;
            if (is_numeric($request->get('emailphone'))) {
                $user->phone = $request->emailphone;
            } else {
                $user->email = $request->emailphone;
            }
            $user->password = Hash::make($request->password);
            if ($user->save()) {
                DB::commit();
                $client = User::with('role')->where('id', $user->id)->first();
                return response()->json(['status' => true, 'Message' => "User Successfully Added", 'user' => $client,], 200);
            } else throw new Error("User Not Added!");
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function login(Request $request)
    {
        $rules = [
            'password' => 'required',
        ];
        if (is_numeric($request->get('emailphone'))) {
            $rules['emailphone'] = 'required|digits:11|exists:users,phone';
        } else {
            $rules['emailphone'] = 'required|email|exists:users,email';
        }
        $valid = Validator::make($request->all(), $rules);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        if (auth()->attempt([
            'email' => $request->emailphone,
            'password' => $request->password,
        ])) {
            $user = auth()->user();
            if ($user->role->name == 'holeseller' || $user->role->name == 'retailer') {
                if ($user->is_active == true) {
                    $token = $user->createToken('token')->accessToken;
                    $user->device_token = request()->token;
                    $user->save();
                    return response()->json(['status' => true, 'Message' => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
                } else {
                    auth()->logout();
                    return response()->json(['status' => false, 'Message' => 'Admin Approval required']);
                }
            } else {
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                $user->save();
                return response()->json(['status' => true, 'Message' => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            }
        } elseif (auth()->attempt([
            'phone' => $request->emailphone,
            'password' => $request->password,
        ])) {
            $user = auth()->user();
            if ($user->role->name == 'holeseller' || $user->role->name == 'retailer') {
                if ($user->is_active == true) {
                    $token = $user->createToken('token')->accessToken;
                    $user->device_token = request()->token;
                    $user->save();
                    return response()->json(['status' => true, 'Message' => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
                } else {
                    auth()->logout();
                    return response()->json(['status' => false, 'Message' => 'Admin Approval required']);
                }
            } else {
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                $user->save();
                return response()->json(['status' => true, 'Message' => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            }
        } else {
            return response()->json(['status' => false, 'Message' => 'Invalid Credentials']);
        }
    }

    public function forgot(Request $request)
    {
        $rules = [];
        if (is_numeric($request->get('emailphone'))) {
            $rules['emailphone'] = 'required|digits:11|exists:users,phone';
        } else {
            $rules['emailphone'] = 'required|email|exists:users,email';
        }
        $valid = Validator::make($request->all(), $rules);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        if (is_numeric($request->get('emailphone'))) {
            $user = User::where('phone', $request->emailphone)->first();
        } else {
            $user = User::where('email', $request->emailphone)->first();
            if (!empty($user)) {
                $user->token = rand(1, 10000);
                $user->save();
                $email = $request->emailphone;
                $token = $user->token;
                Mail::send('admin.mail.appForgotPassword',  compact('email', 'token'), function ($message) use ($email) {
                    $message->to($email);
                    $message->subject('Reset Password');
                });
                return response()->json(['status' => true, 'Message' => "Reset Email send to {$email}", 'token' => $token, 'user' => $user,], 200);
            } else {
                return response()->json(['status' => false, 'Message' => "User not found"]);
            }
        }
    }

    public function reset(Request $request)
    {
        $rules = [
            'token' => 'required',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ];
        if (is_numeric($request->get('emailphone'))) {
            $rules['emailphone'] = 'required|digits:11|exists:users,phone';
        } else {
            $rules['emailphone'] = 'required|email|exists:users,email';
        }
        $valid = Validator::make($request->all(), $rules);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        if (is_numeric($request->get('emailphone'))) {
            $user = User::where('phone', $request->emailphone)->where('token', $request->token)->first();
        } else {
            $user = User::where('email', $request->emailphone)->where('token', $request->token)->first();
            if (empty($user)) return response()->json(['status' => false, 'Message' => "User not found"]);
            if (Hash::check($request->password, $user->password)) return response()->json(['status' => false, 'Message', 'Please use different from current password.']);
            $user->password = Hash::make($request->password);
            $user->token = null;
            $user->save();
            return response()->json(['status' => true, 'Message' => "Password Reset Successfully", 'user' => $user,], 200);
        }
    }

    public function edit_profile()
    {
        $user = User::with('role')->where('id', auth()->user()->id)->first();
        if (!empty($user)) return response()->json(['status' => true, 'Message', 'User found', 'user', $user ?? []], 200);
        else return response()->json(['status' => false, 'Message', 'User not found']);
    }

    public function update_profile(Request $request)
    {
        $user = User::where('id', auth()->user()->id)->first();
        if (!empty($user)) {
            $rules = [
                'name' => 'required',
                'first_name' => 'nullable',
                'last_name' => 'nullable',
            ];
            if (is_numeric($request->get('emailphone'))) {
                $rules['emailphone'] = 'required|digits:11|unique:users,phone,' . auth()->user()->id;
            } else {
                $rules['emailphone'] = 'required|email|unique:users,email,' . auth()->user()->id;
            }
            $valid = Validator::make($request->all(), $rules);
            if ($valid->fails()) {
                return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
            }
            try {
                DB::beginTransaction();
                if (is_numeric($request->get('emailphone'))) {
                    $user->phone = $request->emailphone;
                } else {
                    $user->email = $request->emailphone;
                }
                $user->username = $request->username;
                $user->first_name = $request->fname;
                $user->last_name = $request->lname;
                if (!empty($request->image)) {
                    $image = $request->image;
                    $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                    $image->storeAs('image', $filename, "public");
                    $user->image = "image/" . $filename;
                }
                if ($user->save()) {
                    DB::commit();
                    $user = User::with('role')->where('id', $user->id)->get();
                    return response()->json(['status' => true, 'Message' => "User Successfully Updated", 'user' => $user,], 200);
                } else throw new Error("User Not Updated");
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json(['status' => false, 'Message' => $th->getMessage()]);
            }
        } else return response()->json(['status' => false, 'Message', 'User not found']);
    }

    public function shopFollow(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'shop_id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $followExist = FollowUserShop::where('user_id', auth()->user()->id)->where('shop_id', $request->shop_id)->first();
        if (is_object($followExist)) {
            if ($followExist->delete()) return response()->json(['status' => true, 'Message' => "Unfollow Successfully"], 200);
            return response()->json(['status' => false, 'Message' => "Unfollow not Successfull"]);
        }
        $follow = new FollowUserShop();
        $follow->user_id = auth()->user()->id;
        $follow->shop_id = $request->shop_id;
        if ($follow->save()) return response()->json(['status' => true, 'Message' => "Follow Successfully"], 200);
        return response()->json(['status' => false, 'Message' => "Follow not Successfull"]);
    }

    public function userBlock($id)
    {
        if (empty($id)) return response()->json(['status' => false, 'Message' => 'Id not found']);
        $user = User::where('id', $id)->first();
        if (empty($user)) return response()->json(['status' => false, 'Message' => 'User not found']);
        if ($user->is_block == false) $user->is_block = true;
        $user->is_block = false;
        if ($user->save()) return response()->json(['status' => true, 'Message' => 'User Block Successfully', 'User' => $user ?? []]);
    }
}
