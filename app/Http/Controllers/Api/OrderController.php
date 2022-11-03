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

class OrderController extends Controller
{
    public function show($status = null, $role = null)
    {
        $order = [];
        if ($role == 'retailer') {
            $order = Order::with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('status', $status)
                ->whereHas('seller', function ($q) {
                    $q->whereRelation('role', 'name', 'retailer');
                })->get();
        }
        if ($role == 'wholesaler') {
            $order = Order::with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('status', $status)
                ->whereHas('seller', function ($q) {
                    $q->whereRelation('role', 'name', 'wholesaler');
                })->get();
        }
        if ($role == null) {
            $order = Order::with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('status', $status)->get();
        }
        if (count($order)) return response()->json(['status' => true, 'Message' => 'Order found', 'Orders' => OrderResource::collection($order)], 200);
        else return response()->json(['status' => false, 'Message' => 'Order not found', 'Orders' => $order ?? []]);
    }

    public function userOrder($status = null)
    {
        $order = Order::with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('user_id', auth()->user()->id)->where('status', $status)->get();
        if (count($order)) return response()->json(['status' => true, 'Message' => 'Order found', 'Orders' => OrderResource::collection($order)], 200);
        else return response()->json(['status' => false, 'Message' => 'Order not found', 'Orders' => $order ?? []]);
    }

    public function sellerOrder($status = null)
    {
        $order = Order::with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('seller_id', auth()->user()->id)->where('status', $status)->get();
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
                foreach ($request->order as $key => $orders) {
                    if (is_object($orders)) $orders = $orders->toArray();
                    $order = new Order();
                    $order->user_id = auth()->user()->id;
                    $order->seller_id = $orders['sellerId'];
                    $order->customer_name = $orders['name'];
                    $order->email = $orders['email'];
                    $order->phone = $orders['phone'];
                    // $order->area = $orders['area'];
                    // $order->city = $orders['city'];
                    $order->delivery_address = $orders['address'];
                    $order->order_date = Carbon::now();
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
                            $order_product->subtotal = $product['product_selected_qty'] * $product_price->price;
                            $order_product->discount = $product_price->discount_price * $product['product_selected_qty'];
                            $order_product->save();
                            $total += ($product['product_selected_qty'] * $product_price->price) - ($product_price->discount_price * $product['product_selected_qty']);
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
}
