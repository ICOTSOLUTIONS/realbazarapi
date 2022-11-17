<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\CnicImage;
use App\Models\FollowUserShop;
use App\Models\Package;
use App\Models\PackagePayment;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Api\NotiSend;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function wholesaler(Request $request)
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
        $search = $request->search;
        $wholesaler = User::with('role', 'cnic_image')->where('role_id', 4);
        $wholesaler_count = User::with('role')->where('role_id', 4);
        if (!empty($search)) {
            $wholesaler->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('address2', 'like', '%' . $search . '%')
                    ->orWhere('business_name', 'like', '%' . $search . '%')
                    ->orWhere('business_address', 'like', '%' . $search . '%')
                    ->orWhere('province', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%')
                    ->orWhere('cnic_number', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
            $wholesaler_count->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('address2', 'like', '%' . $search . '%')
                    ->orWhere('business_name', 'like', '%' . $search . '%')
                    ->orWhere('business_address', 'like', '%' . $search . '%')
                    ->orWhere('province', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%')
                    ->orWhere('cnic_number', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        $wholesalers = $wholesaler->skip($skip)->take($take)->orderBy('id', 'DESC')->get();
        $wholesaler_counts = $wholesaler_count->count();
        if (count($wholesalers)) return response()->json(['status' => true, 'Message' => 'Wholesalers found', 'wholesalers' => $wholesalers ?? [], 'wholesalersCount' => $wholesaler_counts ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'Wholesalers not found', 'wholesalers' => $wholesalers ?? [], 'wholesalersCount' => $wholesaler_counts ?? []]);
    }

    public function user(Request $request)
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
        $search = $request->search;
        $user = User::with('role')->where('role_id', 3);
        $user_count = User::with('role')->where('role_id', 3);
        if (!empty($search)) {
            $user->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('address2', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
            $user_count->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('address2', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        $users = $user->skip($skip)->take($take)->orderBy('id', 'DESC')->get();
        $user_counts = $user_count->count();
        if (count($users)) return response()->json(['status' => true, 'Message' => 'Users found', 'users' => $users ?? [], 'usersCount' => $user_counts ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'Users not found', 'users' => $users ?? [], 'usersCount' => $user_counts ?? []]);
    }

    public function retailer(Request $request)
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
        $search = $request->search;
        $retailer = User::with(['role', 'cnic_image'])->where('role_id', 5);
        $retailer_count = User::with('role')->where('role_id', 5);
        if (!empty($search)) {
            $retailer->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('address2', 'like', '%' . $search . '%')
                    ->orWhere('business_name', 'like', '%' . $search . '%')
                    ->orWhere('business_address', 'like', '%' . $search . '%')
                    ->orWhere('province', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%')
                    ->orWhere('cnic_number', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
            $retailer_count->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('address2', 'like', '%' . $search . '%')
                    ->orWhere('business_name', 'like', '%' . $search . '%')
                    ->orWhere('business_address', 'like', '%' . $search . '%')
                    ->orWhere('province', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%')
                    ->orWhere('cnic_number', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        $retailers = $retailer->skip($skip)->take($take)->orderBy('id', 'DESC')->get();
        $retailer_counts = $retailer_count->count();
        if (count($retailers)) return response()->json(['status' => true, 'Message' => 'Retailers found', 'retailers' => $retailers ?? [], 'retailersCount' => $retailer_counts ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'Retailers not found', 'retailers' => $retailers ?? [], 'retailersCount' => $retailer_counts ?? []]);
    }

    public function signup(Request $request)
    {
        $rules = [
            'role' => 'required',
            'name' => 'required',
            'password' => 'required',
        ];
        if ($request->role == 'retailer' || $request->role == 'wholesaler') {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['phone'] = 'required|digits:11|unique:users,phone';
            $rules['business_name'] = 'required';
            $rules['business_address'] = 'required';
            $rules['province'] = 'required';
            $rules['country'] = 'required';
            $rules['cnic_number'] = 'required|digits:13';
            $rules['cnic_image'] = 'required|array';
            $rules['bill_image'] = 'required|image';
        } else {
            if (is_numeric($request->get('emailphone'))) {
                $rules['emailphone'] = 'required|digits:11|unique:users,email';
            } else {
                $rules['emailphone'] = 'required|email|unique:users,email';
            }
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
            $user->password = Hash::make($request->password);
            // $user->address =  $request->address;
            // $user->last_name =  $request->last_name;
            if ($request->role == 'retailer' || $request->role == 'wholesaler') {
                $user->email = $request->email;
                $user->phone = $request->phone;
                $user->business_name = $request->business_name;
                $user->business_address = $request->business_address;
                $user->province = $request->province;
                $user->country = $request->country;
                $user->cnic_number = $request->cnic_number;
                if (!empty($request->hasFile('bill_image'))) {
                    $image = $request->file('bill_image');
                    $filename = "BillImage-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                    $image->storeAs('bill', $filename, "public");
                    $user->bill_image = "bill/" . $filename;
                }
            } else {
                if (is_numeric($request->get('emailphone'))) {
                    $user->phone = $request->emailphone;
                } else {
                    $user->email = $request->emailphone;
                }
                $user->is_user_app = true;
            }
            if (!$user->save()) throw new Error("User Not Added!");
            if ($user->role->name == 'wholesaler' || $user->role->name == 'retailer') {
                if (!empty($request->cnic_image)) {
                    foreach ($request->cnic_image as $key => $images) {
                        $cnic_image = new CnicImage();
                        $filename = "CNICImage-" . time() . "-" . rand() . "." . $images->getClientOriginalExtension();
                        $images->storeAs('cnic', $filename, "public");
                        $cnic_image->user_id = $user->id;
                        $cnic_image->cnic_image = "cnic/" . $filename;
                        if (!$cnic_image->save()) throw new Error("CNIC Images not added!");
                    }
                }
                $package = Package::first();
                if (empty($package)) throw new Error("Free Package is missing Contact with Admin!");
                $date = Carbon::now();
                $paymentPackage = new PackagePayment();
                if ($package->period == 'month' || $package->period == 'Month') $end_date = Carbon::now()->addMonths($package->time);
                $paymentPackage->user_id = $user->id;
                $paymentPackage->package_id = $package->id;
                $paymentPackage->start_date = $date;
                $paymentPackage->end_date = $end_date;
                $paymentPackage->updated_product_qty = $package->product_qty;
                if (!$paymentPackage->save()) throw new Error('Free Package not Buy');
                $statusActive = User::find($user->id);
                if (!$statusActive) throw new Error('User not found after buy package');
                $statusActive->is_active = true;
                if (!$statusActive->save()) throw new Error('User Status not change after buy package');
            }
            $client = User::with(['role', 'cnic_image'])->where('id', $user->id)->first();
            DB::commit();
            return response()->json(['status' => true, 'Message' => "User Successfully Added", 'user' => $client,], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function login(Request $request)
    {
        $rules = [
            'password' => 'required',
            // 'role' => 'required',
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
        // $role = Role::where('name',$request->role)->first();
        if (auth()->attempt([
            'email' => $request->emailphone,
            'password' => $request->password,
            // 'is_block' => false,
        ])) {
            $user = auth()->user()->load('role');
            // if ($user->role->name == 'wholesaler' || $user->role->name == 'retailer') {
            //     // if ($user->is_active == true) {
            //         $token = $user->createToken('token')->accessToken;
            //         $user->device_token = request()->token;
            //         $user->save();
            //         return response()->json(['status' => true, 'Message' => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            //     // } else {
            //     //     auth()->logout();
            //     //     return response()->json(['status' => false, 'Message' => 'Admin Approval required']);
            //     // }
            // } else {
            if ($user->is_block == true) return response()->json(['status' => false, 'Message' => 'Your Status has been Blocked']);
            $token = $user->createToken('token')->accessToken;
            $user->device_token = request()->token;
            $user->save();
            return response()->json(['status' => true, 'Message' => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            // }
        } elseif (auth()->attempt([
            'phone' => $request->emailphone,
            'password' => $request->password,
            // 'is_block' => false,
        ])) {
            $user = auth()->user()->load('role');
            // // if ($user->role->name == 'holeseller' || $user->role->name == 'retailer') {
            //     if ($user->is_active == true) {
            //         $token = $user->createToken('token')->accessToken;
            //         $user->device_token = request()->token;
            //         $user->save();
            //         return response()->json(['status' => true, 'Message' => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            //     } else {
            //         auth()->logout();
            //         return response()->json(['status' => false, 'Message' => 'Admin Approval required']);
            //     }
            // } else {
            if ($user->is_block == true) return response()->json(['status' => false, 'Message' => 'Your Status has been Blocked']);
            $token = $user->createToken('token')->accessToken;
            $user->device_token = request()->token;
            $user->save();
            return response()->json(['status' => true, 'Message' => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            // }
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
        if (!empty($user)) return response()->json(['status' => true, 'Message' => 'User found', 'user' => $user ?? []], 200);
        else return response()->json(['status' => false, 'Message', 'User not found']);
    }

    public function update_profile(Request $request)
    {
        $user = User::where('id', auth()->user()->id)->first();
        if (!empty($user)) {
            $rules = [
                'username' => 'required',
            ];
            if ($user->role->name == 'retailer' || $user->role->name == 'wholesaler') {
                $rules['email'] = 'required|email|unique:users,email,' . auth()->user()->id;
                $rules['phone'] = 'required|digits:11|unique:users,phone,' . auth()->user()->id;
                $rules['business_name'] = 'required';
                $rules['business_address'] = 'required';
                $rules['province'] = 'required';
                $rules['country'] = 'required';
                $rules['shop_number'] = 'nullable';
                $rules['market_name'] = 'nullable';
                $rules['cnic_number'] = 'required|digits:13';
                $rules['cnic_image'] = 'nullable|array';
                $rules['bill_image'] = 'nullable|image';
            } else {
                if (is_numeric($request->get('emailphone'))) {
                    $rules['emailphone'] = 'required|digits:11|unique:users,phone,' . auth()->user()->id;
                } else {
                    $rules['emailphone'] = 'required|email|unique:users,email,' . auth()->user()->id;
                }
            }
            $valid = Validator::make($request->all(), $rules);
            if ($valid->fails()) {
                return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
            }
            try {
                DB::beginTransaction();
                $user->username = $request->username;
                if ($user->role->name == 'wholesaler' || $user->role->name == 'retailer') {
                    $referr_code = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8);
                    dd($referr_code);
                    $user->email = $request->email;
                    $user->phone = $request->phone;
                    $user->business_name = $request->business_name;
                    $user->business_address = $request->business_address;
                    $user->province = $request->province;
                    $user->country = $request->country;
                    $user->shop_number = $request->shop_number;
                    $user->market_name = $request->market_name;
                    $user->cnic_number = $request->cnic_number;
                    if (!empty($referr_code)) $user->referral_code = $referr_code;
                    if (!empty($request->hasFile('bill_image'))) {
                        $image = $request->file('bill_image');
                        $filename = "BillImage-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                        $image->storeAs('bill', $filename, "public");
                        $user->bill_image = "bill/" . $filename;
                    }
                    if (!empty($request->referral_code)) {
                        $referr_user = User::where('referral_code',$request->referral_code)->first();
                        if(empty($referr_user)) throw new Error('Referral Code not valid');
                        $referr_user->referral_count += 1;
                        if(!$referr_user->save()) throw new Error('User not Register to this Referral Code');
                    }
                } else {
                    if (is_numeric($request->get('emailphone'))) {
                        $user->phone = $request->emailphone;
                    } else {
                        $user->email = $request->emailphone;
                    }
                    $user->first_name = $request->first_name;
                    $user->last_name = $request->last_name;
                    $user->address = $request->address;
                    $user->address2 = $request->address2;
                }
                if (!empty($request->image)) {
                    $image = $request->image;
                    $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                    $image->storeAs('image', $filename, "public");
                    $user->image = "image/" . $filename;
                }
                if (!$user->save()) throw new Error("User Not Updated");
                if ($user->role->name == 'wholesaler' || $user->role->name == 'retailer') {
                    if (!empty($request->cnic_image)) {
                        $cnic_images = CnicImage::where('user_id', $user->id)->get();
                        foreach ($cnic_images as $value) {
                            if (!$value->delete()) throw new Error("CNIC Images not deleted!");
                        }
                        foreach ($request->cnic_image as $key => $images) {
                            $cnic_image = new CnicImage();
                            $filename = "CNICImage-" . time() . "-" . rand() . "." . $images->getClientOriginalExtension();
                            $images->storeAs('cnic', $filename, "public");
                            $cnic_image->user_id = $user->id;
                            $cnic_image->cnic_image = "cnic/" . $filename;
                            if (!$cnic_image->save()) throw new Error("CNIC Images not added!");
                        }
                    }
                }
                $updatedUser = User::with(['role', 'cnic_image'])->where('id', $user->id)->first();
                DB::commit();
                return response()->json(['status' => true, 'Message' => "User Successfully Updated", 'user' => $updatedUser,], 200);
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

    public function userBlock($id, $message = null)
    {
        if (empty($id)) return response()->json(['status' => false, 'Message' => 'Id not found']);
        $user = User::where('id', $id)->first();
        if (empty($user)) return response()->json(['status' => false, 'Message' => 'User not found']);
        if ($user->is_block == false) {
            $user->is_block = true;
            $title = 'YOU HAVE BEEN BLOCKED';
            $appnot = new AppNotification();
            $appnot->user_id = $user->id;
            $appnot->notification = $message;
            $appnot->navigation = $title;
            $appnot->save();
            NotiSend::sendNotif($user->device_token, $title, $message);
        } else {
            $user->is_block = false;
            $title = 'YOU HAVE BEEN UNBLOCKED';
            $message = 'Dear ' . $user->username . ' you have been unblocked from admin-The Real Bazaar';
            $appnot = new AppNotification();
            $appnot->user_id = $user->id;
            $appnot->notification = $message;
            $appnot->navigation = $title;
            $appnot->save();
            NotiSend::sendNotif($user->device_token, $title, $message);
        }
        if ($user->save()) return response()->json(['status' => true, 'Message' => 'User Block Successfully', 'User' => $user ?? []]);
    }

    public function show($id)
    {
        if (empty($id)) return response()->json(['status' => false, 'Message' => 'Id not found']);
        $user = User::with('role')->where('id', $id)->first();
        if (!empty($user)) return response()->json(['status' => true, 'Message' => 'User found', 'user' => $user ?? []], 200);
        else return response()->json(['status' => false, 'Message', 'User not found']);
    }
}
