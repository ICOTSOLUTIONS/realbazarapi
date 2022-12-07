<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\AppNotification;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\NotiSend;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class OrderController extends Controller
{
    public function ref()
    {
        dd(session()->all());
    }
    public function show(Request $request)
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
        $status = $request->status;
        $order = Order::orderBy('id', 'DESC')->with(['user_orders.products.images', 'user_payments.payments', 'users.role', 'seller.role']);
        $order_count = Order::with(['user_orders.products.images', 'user_payments.payments', 'users.role', 'seller.role']);
        if (!empty($status)) {
            $order->where('status', $status);
            $order_count->where('status', $status);
        }
        if (!empty($role)) {
            $order->whereHas('users', function ($q) use ($role) {
                $q->whereRelation('role', 'name', $role);
            });
            $order_count->whereHas('users', function ($q) use ($role) {
                $q->whereRelation('role', 'name', $role);
            });
        }
        if (!empty($search)) {
            $order->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('delivery_address', 'like', '%' . $search . '%')
                    ->orWhere('order_date', 'like', '%' . $search . '%');
            });
            $order_count->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('delivery_address', 'like', '%' . $search . '%')
                    ->orWhere('order_date', 'like', '%' . $search . '%');
            });
        }
        $orders = $order->skip($skip)->take($take)->get();
        $orders_counts = $order_count->count();
        if (count($orders)) return response()->json(['status' => true, 'Message' => 'Order found', 'Orders' => OrderResource::collection($orders) ?? [], 'OrdersCount' => $orders_counts ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Order not found', 'Orders' => $orders ?? [], 'OrdersCount' => $orders_counts ?? []]);
    }

    public function userOrder($status = null)
    {
        if ($status == null) {
            $order = Order::orderBy('id', 'DESC')->with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('user_id', auth()->user()->id)->get();
        } else {
            $order = Order::orderBy('id', 'DESC')->with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('user_id', auth()->user()->id)->where('status', $status)->get();
        }
        if (count($order)) return response()->json(['status' => true, 'Message' => 'Order found', 'Orders' => OrderResource::collection($order)], 200);
        else return response()->json(['status' => false, 'Message' => 'Order not found', 'Orders' => $order ?? []]);
    }

    public function sellerOrder($status = null)
    {
        if ($status == null) {
            $order = Order::orderBy('id', 'DESC')->with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('seller_id', auth()->user()->id)->get();
        } else {
            $order = Order::orderBy('id', 'DESC')->with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('seller_id', auth()->user()->id)->where('status', $status)->get();
        }
        if (count($order)) return response()->json(['status' => true, 'Message' => 'Order found', 'Orders' => OrderResource::collection($order)], 200);
        else return response()->json(['status' => false, 'Message' => 'Order not found', 'Orders' => $order ?? []]);
    }

    public function order(Request $request)
    {
        if (!empty($request->order)) {
            try {
                DB::beginTransaction();
                $order_ids = [];
                $total = 0;
                $latestOrderId = 0;
                $latestOrder = Order::orderBy('created_at', 'DESC')->first();
                foreach ($request->order as $key => $orders) {
                    if (is_object($orders)) $orders = $orders->toArray();
                    $order = new Order();
                    $order->user_id = auth()->user()->id;
                    $order->seller_id = $orders['sellerId'];
                    if (empty($latestOrder)) $latestOrderId = 0;
                    else $latestOrderId = $latestOrder->id;
                    $order->order_number = '#' . str_pad($latestOrderId + 1, 8, "0", STR_PAD_LEFT);
                    $order->customer_name = $orders['name'];
                    $order->email = $orders['email'];
                    $order->phone = $orders['phone'];
                    $order->delivery_address = $orders['address'];
                    $order->order_date = Carbon::now();
                    // $order->area = $orders['area'];
                    // $order->city = $orders['city'];
                    // $order->gross_amount = $orders['gross_amount'];
                    // $order->net_amount = $orders['net_amount'];
                    // $order->note = $orders['note'];
                    $order->save();
                    $order_ids[] = $order->id;
                    if (!empty($orders['product'])) {
                        foreach ($orders['product'] as $key => $product) {
                            if (is_object($product)) $product = $product->toArray();
                            $product_price = Product::where('id', $product['id'])->first();
                            $order_product = new OrderProduct();
                            $order_product->order_id = $order->id;
                            $order_product->product_id = $product['id'];
                            $order_product->qty = $product['product_selected_qty'];
                            $order_product->size = $product['size'];
                            $order_product->product_price = $product['product_price'];
                            $order_product->subtotal = $product['product_selected_qty'] * $product['product_price'];
                            $order_product->discount = $product_price->discount_price * $product['product_selected_qty'];
                            $order_product->save();
                            $total += ($product['product_selected_qty'] * $product['product_price']) - ($product_price->discount_price * $product['product_selected_qty']);
                        }
                    } else throw new Error("Order Request Failed!");
                }
                if ($total < 0) throw new Error("Order Request Failed because your total amount is 0!");
                $payment = new Payment();
                $payment->payment_method = $request->payment_method;
                // if ($request->payment_method == "stripe") {
                //     Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                //     $charge = Stripe\Charge::create([
                //         "amount" => round($request->total, 2) * 100,
                //         "currency" => "usd",
                //         "source" => $request->token['id'],
                //         "description" => "Test payment from HNHTECHSOLUTIONS."
                //     ]);
                //     $payment->stripe_id = $charge->id;
                //     $payment->brand = $request->token['brand'];
                //     $payment->card = $request->token['last4'];
                // }
                $payment->total = $total;
                $payment->save();
                $payment->orders()->sync($order_ids);
                $user = User::whereRelation('role', 'name', 'admin')->first();
                $title = 'NEW ORDER';
                $message = 'You have recieved new order';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, $title, $message);
                DB::commit();
                return response()->json(['status' => true, 'Message' => 'New Order Placed!'], 200);
            } catch (\Throwable $th) {
                DB::rollBack();
                // throw $th;
                return response()->json(['status' => false, 'Message' => $th->getMessage(), 'request' => $request->all()]);
            }
        } else return response()->json(['status' => false, 'Message' => 'Order Request Failed!', 'request' => $request->all()]);
    }

    public function orderStatusChange(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $order = Order::where('id', $request->id)->first();
        $order->status = $request->status;
        if ($order->save()) {
            $user = $order->users;
            if ($order->status == 'delivered') {
                $title = 'YOUR ORDER HAS BEEN DELIVERED';
                $message = 'Dear ' . $user->username . ' your order has been delivered from admin-The Real Bazaar';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, $title, $message);
                return response()->json(["status" => true, 'Message' => 'Order Status Change to Delivered Successfully'], 200);
            } elseif ($order->status == 'inprocess') {
                $title = 'YOUR ORDER HAS BEEN In Process';
                $message = 'Dear ' . $user->username . ' your order has been InProcess from admin-The Real Bazaar';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, $title, $request->message);
                return response()->json(["status" => true, 'Message' => 'Order Status Change to In Process Successfully'], 200);
            } elseif ($order->status == 'rejected') {
                $title = 'YOUR ORDER HAS BEEN REJECTED';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $request->message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, $title, $request->message);
                return response()->json(["status" => true, 'Message' => 'Order Status Change to Reject Successfully'], 200);
            } else {
                $title = 'YOUR ORDER HAS BEEN PENDING';
                $message = 'Dear ' . $user->username . ' your order has been pending from admin-The Real Bazaar';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, $title, $message);
                return response()->json(["status" => true, 'Message' => 'Order Status Change to Pending Successfully'], 200);
            }
        } else return response()->json(["status" => false, 'Message' => 'Order Status Change not Successfully']);
    }

    public function jazzcashCheckout(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'price' => 'required|gt:0',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $price = $request->price ?? 0;

        //NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN
        //1.
        //get formatted price. remove period(.) from the price
        $pp_Amount     = $price * 100;

        //NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN
        //2.
        //get the current date and time
        //be careful set TimeZone in config/app.php
        $DateTime         = Carbon::now();
        $pp_TxnDateTime = $DateTime->format('YmdHis');

        //NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN
        //3.
        //to make expiry date and time add one hour to current date and time
        $ExpiryDateTime = $DateTime;
        $ExpiryDateTime->modify('+' . 1 . ' hours');
        $pp_TxnExpiryDateTime = $ExpiryDateTime->format('YmdHis');

        //NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN
        //4.
        //make unique transaction id using current date
        $pp_TxnRefNo = 'T' . $pp_TxnDateTime;


        //--------------------------------------------------------------------------------
        //postData
        $post_data =  array(
            "pp_Version"             => Config::get('jazzcashCheckout.jazzcash.VERSION'),
            "pp_TxnType"             => "",
            "pp_Language"             => Config::get('jazzcashCheckout.jazzcash.LANGUAGE'),
            "pp_MerchantID"         => Config::get('jazzcashCheckout.jazzcash.MERCHANT_ID'),
            "pp_SubMerchantID"         => "",
            "pp_Password"             => Config::get('jazzcashCheckout.jazzcash.PASSWORD'),
            "pp_BankID"             => "TBANK",
            "pp_TxnRefNo"             => $pp_TxnRefNo,
            "pp_Amount"             => $pp_Amount,
            "pp_TxnCurrency"         => Config::get('jazzcashCheckout.jazzcash.CURRENCY_CODE'),
            "pp_TxnDateTime"         => $pp_TxnDateTime,
            "pp_BillReference"         => "billRef",
            "pp_Description"         => "Description of transaction",
            "pp_TxnExpiryDateTime"     => $pp_TxnExpiryDateTime,
            "pp_ReturnURL"             => Config::get('jazzcashCheckout.jazzcash.RETURN_URL'),
            "pp_SecureHash"         => "",
            "ppmpf_1"                 => "1",
            "ppmpf_2"                 => "2",
            "ppmpf_3"                 => "3",
            "ppmpf_4"                 => "4",
            "ppmpf_5"                 => "5",
        );

        $pp_SecureHash = $this->get_SecureHash($post_data);

        $post_data['pp_SecureHash'] = $pp_SecureHash;
        session()->put('ref_no',$pp_TxnRefNo);
        return view('do_checkout_v', ['post_data' => $post_data]);
        // if (count($post_data)) return response()->json(['status' => true,  'url' => Config::get('jazzcashCheckout.jazzcash.TRANSACTION_POST_URL') ?? [], 'data' => $post_data ?? []], 200);
        // else return response()->json(['status' => false,  'Message' => 'Request Failed']);
    }

    public function jazzcashCardRefund(Request $request)
    {
        // $valid = Validator::make($request->all(), [
        //     'price' => 'required|gt:0',
        // ]);

        // if ($valid->fails()) {
        //     return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        // }

        // $price = $request->price ?? 0;
        $price = 100;

        //NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN
        //1.
        //get formatted price. remove period(.) from the price
        $pp_Amount     = $price * 100;

        //NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN
        //2.
        //get the current date and time
        //be careful set TimeZone in config/app.php
        $DateTime         = Carbon::now();
        $pp_TxnDateTime = $DateTime->format('YmdH');

        // //NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN
        // //3.
        // //to make expiry date and time add one hour to current date and time
        // $ExpiryDateTime = $DateTime;
        // $ExpiryDateTime->modify('+' . 1 . ' hours');
        // $pp_TxnExpiryDateTime = $ExpiryDateTime->format('YmdHis');

        //NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN
        //4.
        //make unique transaction id using current date
        $pp_TxnRefNo = 'T' . $pp_TxnDateTime;


        //--------------------------------------------------------------------------------
        //postData
        $post_data =  array(
            "pp_TxnRefNo"             => $pp_TxnRefNo,
            "pp_Amount"             => $pp_Amount,
            "pp_TxnCurrency"         => Config::get('jazzcashCheckout.jazzcash.CURRENCY_CODE'),
            "pp_MerchantID"         => Config::get('jazzcashCheckout.jazzcash.MERCHANT_ID'),
            "pp_Password"             => Config::get('jazzcashCheckout.jazzcash.PASSWORD'),
            // "pp_MerchantMPIN"             => Config::get('jazzcashCheckout.jazzcash.MerchantMPIN'),
            "pp_SecureHash"         => "",
        );

        $pp_SecureHash = $this->get_SecureHash($post_data);
        $post_data['pp_SecureHash'] = $pp_SecureHash;
        return view('do_checkout_v', ['post_data' => $post_data]);
        // if (count($post_data)) return response()->json(['status' => true,  'url' => Config::get('jazzcashCheckout.jazzcash.CARD_REFUND_POST_URL') ?? [], 'data' => $post_data ?? []], 200);
        // else return response()->json(['status' => false,  'Message' => 'Request Failed']);
    }

    private function get_SecureHash($data_array)
    {
        ksort($data_array);
        $str = '';
        foreach ($data_array as $key => $value) {
            if (!empty($value)) {
                $str = $str . '&' . $value;
            }
        }
        $str = Config::get('jazzcashCheckout.jazzcash.INTEGERITY_SALT') . $str;
        $pp_SecureHash = hash_hmac('sha256', $str, Config::get('jazzcashCheckout.jazzcash.INTEGERITY_SALT'));

        return $pp_SecureHash;
    }

    public function jazzcashPaymentStatus(Request $request)
    {
        $url = Config::get('jazzcashCheckout.jazzcash.WEB_RETURN_URL');
        if (!empty($request->pp_ResponseCode)) {
            if ($request->pp_ResponseCode == 000) {
                return redirect($url . '?response_code=' . $request->pp_ResponseCode . '&response_message=' . $request->pp_ResponseMessage);
            } else {
                return redirect($url . $request->pp_ResponseCode . '&response_message=' . $request->pp_ResponseMessage);
            }
        } else {
            return redirect($url);
        }
    }

    public function easypaisaCheckout(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'price' => 'required|gt:0',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $price = $request->price;

        $amount     = $price * 100;
        $amount     = 10;

        $DateTime         = Carbon::now();
        $dateTime = $DateTime->format('dms');
        $orderRefNum = $dateTime;

        $timestampDateTime = $DateTime;
        $timestamp = $timestampDateTime->format('Y-m-d\TH:i:s');
        //postData
        $postbackurl = urlencode(Config::get('easypaisaCheckout.easypaisa.POST_BACK_URL'));
        $post_data =  array(
            "storeId"             => Config::get('easypaisaCheckout.easypaisa.STORE_ID'),
            "orderId"             => $orderRefNum,
            "transactionAmount"             => $amount,
            "mobileAccountNo"             => "",
            "emailAddress"             => "",
            "transactionType"             => Config::get('easypaisaCheckout.easypaisa.TransactionType'),
            "tokenExpiry"             => "",
            "bankIdentificationNumber"             => "",
            "encryptedHashRequest"         => "",
            "merchantPaymentMethod"             => "",
            "postBackURL"             => $postbackurl,
            "signature"             => "",
        );

        $str = "amount=" . $amount . "&orderRefNum=" . $orderRefNum . "&paymentMethod=" . Config::get('easypaisaCheckout.easypaisa.TransactionType') . "&postBackURL=" . Config::get('easypaisaCheckout.easypaisa.POST_BACK_URL') . "&storeId=" . Config::get('easypaisaCheckout.easypaisa.STORE_ID') . "&timeStamp=" . $timestamp;
        $hashKey = Config::get('easypaisaCheckout.easypaisa.HASH_KEY');
        $cipher = "aes-128-ecb";
        $crypttext = openssl_encrypt($str, $cipher, $hashKey, OPENSSL_RAW_DATA);
        $encryptedHashRequest = base64_encode($crypttext);
        $encryptedHashRequest = urlencode($encryptedHashRequest);
        $post_data['encryptedHashRequest'] = $encryptedHashRequest;
        $param = '';
        $i = 1;

        foreach ($post_data as $key => $value) {
            if (!empty($key)) {
                if ($i == 1) $param = $key . '=' . $value;
                else {
                    $param = $param . '&' . $key . '=' . $value;
                }
            }
            $i++;
        }

        if (count($post_data)) return response()->json(['status' => true, 'url' => Config::get('easypaisaCheckout.easypaisa.TRANSACTION_POST_URL') . $param], 200);
        else return response()->json(['status' => false,  'Message' => 'Request Failed']);
    }
}
