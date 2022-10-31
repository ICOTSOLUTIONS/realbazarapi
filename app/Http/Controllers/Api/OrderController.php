<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stripe;

class OrderController extends Controller
{
    public function show()
    {
        $order = Order::with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->get();
        if (count($order)) return response()->json(['status' => true, 'Message' => 'Order found', 'Orders' => OrderResource::collection($order)], 200);
        else return response()->json(['status' => false, 'Message' => 'Order not found', 'Orders' => $order ?? []]);
    }

    public function userOrder()
    {
        $order = Order::with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('user_id', auth()->user()->id)->get();
        if (count($order)) return response()->json(['status' => true, 'Message' => 'Order found', 'Orders' => OrderResource::collection($order)], 200);
        else return response()->json(['status' => false, 'Message' => 'Order not found', 'Orders' => $order ?? []]);
    }

    public function sellerOrder()
    {
        $order = Order::with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('seller_id', auth()->user()->id)->get();
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
            if ($order->status == 'delivered') return response()->json(["status" => true, 'Message' => 'Order Status Change to Delivered Successfully'], 200);
            elseif ($order->status == 'accept') return response()->json(["status" => true, 'Message' => 'Order Status Change to Accept Successfully'], 200);
            else return response()->json(["status" => true, 'Message' => 'Order Status Change to Pending Successfully'], 200);
        } else return response()->json(["status" => false, 'Message' => 'Order Status Change not Successfully']);
    }
}
