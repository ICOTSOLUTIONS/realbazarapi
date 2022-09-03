<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductsResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SubCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function show()
    {
        $all_product = Product::has('user')->with('user', 'images', 'subCategories.categories')->get();
        return response()->json(['Products' => ProductsResource::collection($all_product)], 200);
    }

    public function vendorProduct()
    {
        $all_product = Product::has('user')->with('user', 'images', 'subCategories.categories')->where('user_id', auth()->user()->id)->get();
        return response()->json(['Products' => ProductsResource::collection($all_product)], 200);
    }

    public function vendorFeaturedProduct()
    {
        $product = Product::with('user')->where('featured', 'Featured')->where('status', 'active')->get();
        return response()->json(['status' => 'success', 'products' => $product ?? []], 200);
    }

    public function showProduct(Request $request)
    {
        $all_product = Product::where('id', $request->id)->has('user')->with('user', 'images', 'subCategories.categories')->get();
        return response()->json(['Products' => ProductsResource::collection($all_product)], 200);
    }

    public function add(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_name' => 'required',
            'price' => 'required',
            'discount' => 'required',
            'size' => 'required',
            'brand' => 'required',
            'product_status' => 'required',
            'product_selected_qty' => 'nullable',
            'product_details' => 'required',
            'product_image' => 'required|array',
            'category' => 'required',
            'featured' => 'required',
            'sub_category' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => 'fails', 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        if (auth()->user()->role_id == 3 || auth()->user()->role_id == 4) {
            $new_product = new Product();
            $new_product->user_id = auth()->user()->id;
            if ($request->category && $request->sub_category) {
                $category = Category::where('name', $request->category)->first();
                if (!is_object($category)) {
                    $category = new Category();
                    $category->name = $request->category;
                    $category->url = strtolower(preg_replace('/\s*/', '', $request->category));
                    $category->save();

                    $subcategory = new SubCategory();
                    $subcategory->category_id = $category->id;
                    $subcategory->name = $request->sub_category;
                    $subcategory->url = strtolower(preg_replace('/\s*/', '', $request->category . '/' . $request->sub_category));
                    $subcategory->save();
                } else {
                    $subcategory = SubCategory::whereHas('categories', function ($query) use ($category) {
                        $query->where('id', $category->id);
                    })->where('name', $request->sub_category)->first();
                    if (!is_object($subcategory)) {
                        $subcategory = new SubCategory();
                        $subcategory->category_id = $category->id;
                        $subcategory->name = $request->sub_category;
                        $subcategory->url = strtolower(preg_replace('/\s*/', '', $request->category . '/' . $request->sub_category));
                        $subcategory->save();
                    }
                }
                $new_product->sub_category_id = $subcategory->id;
            }
            $new_product->name = $request->product_name;
            $new_product->price = $request->price;
            $new_product->discount_price = $request->discount;
            $new_product->size = $request->size;
            $new_product->brand = $request->brand;
            $new_product->type = $request->product_status;
            $new_product->details = $request->product_details;
            $new_product->featured = $request->featured;
            if ($request->featured == "Featured") {
                $new_product->status = "pending";
            }

            $new_product->save();
            if (!empty($request->product_image)) {
                foreach ($request->product_image as $image) {
                    $product_image = new ProductImage();
                    $product_image->product_id = $new_product->id;
                    $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                    $image->storeAs('image', $filename, "public");
                    $product_image->image = "image/" . $filename;
                    $product_image->save();
                }
            }
            return response()->json(['Successfull' => 'New Product Added Successfully!'], 200);
        } else {
            return response()->json(['UnSuccessfull' => 'New Product not Added!'], 500);
        }
    }

    public function search($name)
    {
        if (!empty($name)) {
            $product = Product::where('name', 'LIKE', '%' . $name . '%')->get();
            if (count($product)) {
                return response()->json(['Products' => ProductsResource::collection($product)], 200);
            } else {
                return response()->json(['error' => 'Product not found'], 500);
            }
        } else {
            return response()->json(['error' => 'Parameter is null'], 500);
        }
    }

    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_name' => 'required',
            'price' => 'required',
            'discount' => 'required',
            'size' => 'required',
            'brand' => 'required',
            'product_status' => 'required',
            'product_details' => 'required',
            'featured' => 'required',
            'category' => 'required',
            'sub_category' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => 'fails', 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $product = Product::where('id', $request->id)->first();
        if (auth()->user()->role_id == 3) {
            $product->user_id = auth()->user()->id;
            if ($request->category && $request->sub_category) {
                $category = Category::where('name', $request->category)->first();
                if (!is_object($category)) {
                    $category = new Category();
                    $category->name = $request->category;
                    $category->url = strtolower(preg_replace('/\s*/', '', $request->category));
                    $category->save();

                    $subcategory = new SubCategory();
                    $subcategory->category_id = $category->id;
                    $subcategory->name = $request->sub_category;
                    $subcategory->url = strtolower(preg_replace('/\s*/', '', $request->category . '/' . $request->sub_category));
                    $subcategory->save();
                } else {
                    $subcategory = SubCategory::whereHas('categories', function ($query) use ($request, $category) {
                        $query->where('id', $category->id);
                    })->where('name', $request->sub_category)->first();
                    if (!is_object($subcategory)) {
                        $subcategory = new SubCategory();
                        $subcategory->category_id = $category->id;
                        $subcategory->name = $request->sub_category;
                        $subcategory->url = strtolower(preg_replace('/\s*/', '', $request->category . '/' . $request->sub_category));
                        $subcategory->save();
                    }
                }
                $product->sub_category_id = $subcategory->id;
            }
            $product->name = $request->product_name;
            $product->price = $request->price;
            $product->discount_price = $request->discount;
            $product->size = $request->size;
            $product->brand = $request->brand;
            $product->type = $request->product_status;
            $product->featured = $request->featured;
            if ($request->featured == "Featured") {
                $product->status = "pending";
            } else {
                $product->status = null;
            }
            $product->details = $request->product_details;
            $product->save();
            return response()->json(['Successfull' => 'Product Updated Successfully!'], 200);
        } else {
            return response()->json(['UnSuccessfull' => 'Product not Updated!'], 500);
        }
    }

    public function image($id)
    {
        $all_image = ProductImage::where('product_id', $id)->get();
        return response()->json(['Images' => $all_image], 200);
    }

    public function addImage(Request $request)
    {
        if (!empty($request->product_image)) {
            foreach ($request->product_image as $image) {
                $product_image = new ProductImage();
                $product_image->product_id = $request->product_id;
                $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('image', $filename, "public");
                $product_image->image = "image/" . $filename;
                $product_image->save();
            }
            return response()->json(['Successfull' => 'Product Image Added Successfully!'], 200);
        } else {
            return response()->json(['Fail' => 'Product Image not Added!'], 500);
        }
    }

    public function deleteImage(Request $request)
    {
        $product = ProductImage::where('id', $request->id)->first();
        if (!empty($product)) {
            if ($product->delete()) return response()->json(['status' => 'successfully Image deleted'], 200);
        } else {
            return response()->json(["status" => 'fail', 500]);
        }
    }

    public function delete(Request $request)
    {
        $product = Product::where('id', $request->id)->first();
        if (!empty($product)) {
            if ($product->delete()) return response()->json(['status' => 'successfully deleted'], 200);
        } else {
            return response()->json(["status" => 'fail', 500]);
        }
    }

    public function seller_totalsales_count()
    {
        $seller_totalsales_count = Order::where('seller_id', auth()->user()->id)->groupBy('seller_id')
            ->selectRaw('seller_id,sum(net_amount) AS net_amount')->get();

        $seller_todaysales_count = Order::where('seller_id', auth()->user()->id)
            ->where('order_date', Carbon::today())
            ->selectRaw('seller_id, sum(net_amount) AS net_amount')->groupBy('seller_id')->get();

        $submonth = Carbon::now();
        $subweek = Carbon::now();

        $seller_lastmonthsales_count = Order::where('seller_id', auth()->user()->id)
            ->where('order_date', '>=', $submonth->submonth())
            ->where('order_date', '<=', Carbon::today())
            ->selectRaw('seller_id, sum(net_amount) AS net_amount')->groupBy('seller_id')->get();
        $seller_lastweeksales_count = Order::where('seller_id', auth()->user()->id)
            ->where('order_date', '>=', $subweek->subweek())
            ->where('order_date', '<=', Carbon::today())
            ->selectRaw('seller_id, sum(net_amount) AS net_amount')->groupBy('seller_id')->get();
        return response()->json(["status" => 'success', 'totalsales_count' => $seller_totalsales_count, 'lastmonthsales_count' => $seller_lastmonthsales_count, 'todaysales_count' => $seller_todaysales_count, 'lastweeksales_count' => $seller_lastweeksales_count], 200);
    }

    public function seller_products_count()
    {
        $seller_products_count = Product::where('user_id', auth()->user()->id)->count();
        $seller_category_count = SubCategory::with('categories:id,name')->withCount('products')->get();
        return response()->json([
            "status" => 'success', 'products_count' => $seller_products_count,
            'category_count' => $seller_category_count
        ], 200);
    }

    public function seller_top_products()
    {
        $seller_top_products = Product::where('user_id', auth()->user()->id)->withCount('orders')->get();
        $seller_top_products = $seller_top_products->sortByDesc('orders_count')->values();
        return response()->json(["status" => 'success', 'seller_top_products' => $seller_top_products], 200);
    }

    public function seller_top_customers()
    {
        $seller_top_customers = Order::selectRaw('user_id, SUM(net_amount) as total_amount')->with('users')->where('seller_id', auth()->user()->id)->groupBy('user_id')->get();
        $seller_top_customers = $seller_top_customers->sortByDesc('total_amount')->values();
        return response()->json(["status" => 'success', 'seller_top_customers' => $seller_top_customers], 200);
    }

    public function admin_totalsales_count()
    {
        $seller_totalsales_count = Payment::selectRaw('sum(total) AS total')->get();


        $seller_todaysales_count = Payment::whereDate('created_at', Carbon::today())
            ->selectRaw('sum(total) AS total')->get();

        $submonth = Carbon::now();
        $subweek = Carbon::now();

        $seller_lastmonthsales_count = Payment::where('created_at', '>=', $submonth->submonth())
            ->where('created_at', '<=', Carbon::today())
            ->selectRaw('sum(total) AS total')->get();

        $seller_lastweeksales_count = Payment::where('created_at', '>=', $subweek->subweek())
            ->where('created_at', '<=', Carbon::today())
            ->selectRaw('sum(total) AS total')->get();

        return response()->json(["status" => 'success', 'totalsales_count' => $seller_totalsales_count, 'lastmonthsales_count' => $seller_lastmonthsales_count, 'todaysales_count' => $seller_todaysales_count, 'lastweeksales_count' => $seller_lastweeksales_count], 200);
    }

    public function admin_vendor_count()
    {
        $vendor_count = User::whereHas('role', function ($query) {
            $query->where('name', 'seller');
        })->count();
        $vendor_product_count = User::withCount('products')->get();
        $vendor_product_count = $vendor_product_count->sortByDesc('products_count')->values();
        return response()->json([
            "status" => 'success', 'vendors_count' => $vendor_count,
            'vendor_products_count' => $vendor_product_count
        ], 200);
    }

    public function admin_vendor_sales()
    {
        $admin_vendor_sales = Order::selectRaw('seller_id, SUM(net_amount) as total_amount')
            ->with('seller')->groupBy('seller_id')->get();
        $admin_vendor_sales = $admin_vendor_sales->sortByDesc('orders_count')->values();
        return response()->json(["status" => 'success', 'admin_vendor_sales' => $admin_vendor_sales], 200);
    }

    public function admin_customer_count()
    {
        $customer_count = User::whereHas('role', function ($query) {
            $query->where('name', 'user');
        })->count();
        $top_customers = Order::selectRaw('user_id, SUM(net_amount) as total_amount')
            ->with('users')->groupBy('user_id')->get();
        $top_customers = $top_customers->sortByDesc('total_amount')->values();
        return response()->json(["status" => 'success', 'customers_count' => $customer_count, 'top_customers' => $top_customers], 200);
    }

    public function seller_line_chart()
    {
        $lineChart = Order::where('seller_id', auth()->user()->id)
            ->selectRaw("COUNT(*) as orders")
            ->selectRaw("sum(net_amount) as total_amount")
            ->selectRaw("MONTHNAME(created_at) as month_name")
            ->selectRaw("DATE(created_at) as date")
            ->selectRaw('max(created_at) as createdAt')
            ->whereMonth('created_at', date('m'))
            ->groupBy('month_name')
            ->groupBy('date')
            ->orderBy('createdAt')
            ->get();
        return response()->json(["status" => 'success', 'lineChart' => $lineChart], 200);
    }

    public function featureProduct()
    {
        $product = Product::with('user')->where('featured', 'Featured')->get();
        return response()->json(["status" => 'success', 'feature_products' => $product], 200);
    }

    public function featureProductStatusChange($id)
    {
        $product = Product::where('id', $id)->first();
        if ($product->status == "active" && $product->featured == "Featured") {
            $product->status = "pending";
        } elseif ($product->status == "pending" && $product->featured == "Featured") {
            $product->status = "active";
        } else {
            $product->status = null;
        }
        $product->save();
        return response()->json(["status" => 'success', 'feature_products' => $product], 200);
    }
}
