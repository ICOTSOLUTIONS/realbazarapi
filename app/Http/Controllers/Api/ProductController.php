<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductsResource;
use App\Models\Category;
use App\Models\LikeProduct;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductReview;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\UserProductHistory;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function show()
    {
        $all_product = Product::has('user')->with('user', 'images', 'subCategories.categories', 'reviews.users')->get();
        return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($all_product)], 200);
    }

    public function vendorProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $all_product = Product::has('user')->with('user', 'images', 'subCategories.categories', 'reviews.users')->where('user_id', $request->id)->get();
        if (count($all_product)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($all_product)], 200);
        return response()->json(['status' => false, 'Message' => 'Product not found']);
    }

    public function vendorFeaturedProduct()
    {
        $product = Product::with('user')->where('featured', 'Featured')->where('status', 'active')->get();
        return response()->json(['status' => true, 'Message' => 'Product found', 'products' => $product ?? []], 200);
    }

    public function showProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $id = explode(',', $request->id);
        $all_product = Product::whereIn('id', $id)->has('user')->with('user', 'images', 'subCategories.categories', 'reviews.users')->get();
        if (count($all_product)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($all_product)], 200);
        return response()->json(['status' => false, 'Message' => 'Product not found']);
    }

    public function add(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'title' => 'required',
            'price' => 'required',
            'discount' => 'nullable',
            // 'size' => 'required',
            // 'brand' => 'required',
            // 'product_status' => 'required',
            // 'product_selected_qty' => 'nullable',
            'product_desc' => 'required',
            'product_image' => 'required|array',
            // 'category' => 'required',
            // 'featured' => 'required',
            'tags' => 'required',
            'sub_category_id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $user = auth()->user();
            if ($user->role_id == 4 || $user->role_id == 5) {
                $new_product = new Product();
                $new_product->user_id = $user->id;
                // if ($request->category && $request->sub_category) {
                //     $category = Category::where('id', $request->category)->first();
                //     if (!is_object($category)) {
                //         $category = new Category();
                //         $category->name = $request->category;
                //         $category->url = strtolower(preg_replace('/\s*/', '', $request->category));
                //         $category->save();

                //         $subcategory = new SubCategory();
                //         $subcategory->category_id = $category->id;
                //         $subcategory->name = $request->sub_category;
                //         $subcategory->url = strtolower(preg_replace('/\s*/', '', $request->category . '/' . $request->sub_category));
                //         $subcategory->save();
                //     } else {
                //         $subcategory = SubCategory::whereHas('categories', function ($query) use ($category) {
                //             $query->where('id', $category->id);
                //         })->where('name', $request->sub_category)->first();
                //         if (!is_object($subcategory)) {
                //             $subcategory = new SubCategory();
                //             $subcategory->category_id = $category->id;
                //             $subcategory->name = $request->sub_category;
                //             $subcategory->url = strtolower(preg_replace('/\s*/', '', $request->category . '/' . $request->sub_category));
                //             $subcategory->save();
                //         }
                //     }
                // }
                $new_product->sub_category_id = $request->sub_category_id;
                $new_product->title = $request->title;
                $new_product->price = $request->price;
                $new_product->discount_price = $request->discount;
                $new_product->tags = $request->tags;
                $new_product->desc = $request->product_desc;
                // $new_product->size = $request->size;
                // $new_product->brand = $request->brand;
                // $new_product->type = $request->product_status;
                // $new_product->featured = $request->featured;
                // if ($request->featured == "Featured") {
                //     $new_product->status = "pending";
                // }

                if (!$new_product->save()) throw new Error("Product not added!");
                if (!empty($request->product_image)) {
                    foreach ($request->product_image as $image) {
                        $product_image = new ProductImage();
                        $product_image->product_id = $new_product->id;
                        $filename = "Product-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                        $image->storeAs('product', $filename, "public");
                        $product_image->image = "product/" . $filename;
                        if (!$product_image->save()) throw new Error("Product Images not added!");
                    }
                }
                DB::commit();
                return response()->json(['status' => true, 'Message' => 'Product Added Successfully!'], 200);
            } else throw new Error("Authenticated User Required!");
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function search($name)
    {
        if (!empty($name)) {
            $names = explode(',', $name);
            $product = Product::where(function ($query) use ($names) {
                foreach ($names as $tag) {
                    $query->where('title', 'LIKE', '%' . $tag . '%')->orWhere('tags', 'LIKE', '%' . $tag . '%');
                }
            })->get();
            if (count($product)) {
                return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($product)], 200);
            } else {
                return response()->json(['status' => false, 'Message' => 'Product not found', 'Products' => $product??[]]);
            }
        } else {
            return response()->json(['status' => false, 'Message' => 'Parameter is null']);
        }
    }

    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'title' => 'required',
            'price' => 'required',
            'discount' => 'nullable',
            // 'size' => 'required',
            // 'brand' => 'required',
            // 'product_status' => 'required',
            // 'product_selected_qty' => 'nullable',
            'product_desc' => 'required',
            // 'product_image' => 'required|array',
            // 'category' => 'required',
            // 'featured' => 'required',
            'tags' => 'required',
            'sub_category_id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $user = auth()->user();
            if ($user->role_id == 4 || $user->role_id == 5) {
                $product = Product::where('id', $request->id)->first();
                // dd($request->all());
                $product->user_id = $user->id;
                // if ($request->category && $request->sub_category) {
                //     $category = Category::where('name', $request->category)->first();
                //     if (!is_object($category)) {
                //         $category = new Category();
                //         $category->name = $request->category;
                //         $category->url = strtolower(preg_replace('/\s*/', '', $request->category));
                //         $category->save();

                //         $subcategory = new SubCategory();
                //         $subcategory->category_id = $category->id;
                //         $subcategory->name = $request->sub_category;
                //         $subcategory->url = strtolower(preg_replace('/\s*/', '', $request->category . '/' . $request->sub_category));
                //         $subcategory->save();
                //     } else {
                //         $subcategory = SubCategory::whereHas('categories', function ($query) use ($request, $category) {
                //             $query->where('id', $category->id);
                //         })->where('name', $request->sub_category)->first();
                //         if (!is_object($subcategory)) {
                //             $subcategory = new SubCategory();
                //             $subcategory->category_id = $category->id;
                //             $subcategory->name = $request->sub_category;
                //             $subcategory->url = strtolower(preg_replace('/\s*/', '', $request->category . '/' . $request->sub_category));
                //             $subcategory->save();
                //         }
                //     }
                // }
                $product->sub_category_id = $request->sub_category_id;
                $product->title = $request->title;
                $product->price = $request->price;
                $product->discount_price = $request->discount;
                $product->tags = $request->tags;
                $product->desc = $request->product_desc;
                // $product->size = $request->size;
                // $product->brand = $request->brand;
                // $product->type = $request->product_status;
                // $product->featured = $request->featured;
                // if ($request->featured == "Featured") {
                // $product->status = "pending";
                // } else {
                // $product->status = null;
                // }
                // $product->details = $request->product_details;
                if (!$product->save()) throw new Error("Product not updated!");
                DB::commit();
                return response()->json(['status' => true, 'Message' => 'Product Updated Successfully!', 'product' => $product], 200);
            } else throw new Error("Authenticated User Required!");
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function image($id)
    {
        $all_image = ProductImage::where('product_id', $id)->get();
        if (count($all_image)) return response()->json(['status' => true, 'Message' => 'Product Image found', 'Images' => $all_image], 200);
        else return response()->json(['status' => false, 'Message' => 'Product Image not found']);
    }

    public function addImage(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_id' => 'required|numeric',
            'product_image' => 'required|array',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        if (!empty($request->product_image)) {
            foreach ($request->product_image as $image) {
                $product_image = new ProductImage();
                $product_image->product_id = $request->product_id;
                $filename = "Product-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('product', $filename, "public");
                $product_image->image = "product/" . $filename;
                $product_image->save();
            }
            return response()->json(['status' => true, 'Message' => 'Product Image Added Successfully!'], 200);
        } else return response()->json(['status' => false, 'Message' => 'Product Image not Added!']);
    }

    public function deleteImage(Request $request)
    {
        $product = ProductImage::where('id', $request->id)->first();
        if (!empty($product)) {
            if ($product->delete()) return response()->json(['status' => true, 'Message' => 'Successfully Image deleted'], 200);
        } else return response()->json(["status" => false, 'Message' => 'Unsuccessfull Image deleted']);
    }

    public function delete(Request $request)
    {
        $product = Product::where('id', $request->id)->first();
        if (!empty($product)) {
            if ($product->delete()) return response()->json(['status' => true, 'Message' => 'Successfully deleted Product'], 200);
        } else {
            return response()->json(["status" => false, 'Message' => 'Product not deleted']);
        }
    }

    public function historyProduct()
    {
        $historyProduct = Product::has('user')->with('user', 'images', 'subCategories.categories', 'reviews.users')->whereHas('history', function ($query) {
            $query->where('user_id', auth()->user()->id);
        })->get();
        if (count($historyProduct)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($historyProduct)], 200);
        return response()->json(['status' => false, 'Message' => 'Product not found']);
    }

    public function addHistoryProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            // 'user_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $history = UserProductHistory::where('user_id', auth()->user()->id)->where('product_id', $request->product_id)->first();
        if (!empty($history)) return response()->json(['status' => true, 'Message' => 'Users product exist in history'], 200);
        $product = new UserProductHistory();
        $product->user_id = auth()->user()->id;
        $product->product_id = $request->product_id;
        if ($product->save()) return response()->json(['status' => true, 'Message' => 'Users product added in history'], 200);
        return response()->json(['status' => false, 'Message' => 'Users product not added in history']);
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
        return response()->json(["status" => true, 'totalsales_count' => $seller_totalsales_count, 'lastmonthsales_count' => $seller_lastmonthsales_count, 'todaysales_count' => $seller_todaysales_count, 'lastweeksales_count' => $seller_lastweeksales_count], 200);
    }

    public function seller_products_count()
    {
        $seller_products_count = Product::where('user_id', auth()->user()->id)->count();
        $seller_category_count = SubCategory::with('categories:id,name')->withCount('products')->get();
        return response()->json([
            "status" => true, 'products_count' => $seller_products_count,
            'category_count' => $seller_category_count
        ], 200);
    }

    public function seller_top_products()
    {
        $seller_top_products = Product::where('user_id', auth()->user()->id)->withCount('orders')->get();
        $seller_top_products = $seller_top_products->sortByDesc('orders_count')->values();
        return response()->json(["status" => true, 'seller_top_products' => $seller_top_products], 200);
    }

    public function seller_top_customers()
    {
        $seller_top_customers = Order::selectRaw('user_id, SUM(net_amount) as total_amount')->with('users')->where('seller_id', auth()->user()->id)->groupBy('user_id')->get();
        $seller_top_customers = $seller_top_customers->sortByDesc('total_amount')->values();
        return response()->json(["status" => true, 'seller_top_customers' => $seller_top_customers], 200);
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

        return response()->json(["status" => true, 'totalsales_count' => $seller_totalsales_count, 'lastmonthsales_count' => $seller_lastmonthsales_count, 'todaysales_count' => $seller_todaysales_count, 'lastweeksales_count' => $seller_lastweeksales_count], 200);
    }

    public function admin_vendor_count()
    {
        $vendor_count = User::whereHas('role', function ($query) {
            $query->where('name', 'seller');
        })->count();
        $vendor_product_count = User::withCount('products')->get();
        $vendor_product_count = $vendor_product_count->sortByDesc('products_count')->values();
        return response()->json([
            "status" => true, 'vendors_count' => $vendor_count,
            'vendor_products_count' => $vendor_product_count
        ], 200);
    }

    public function admin_vendor_sales()
    {
        $admin_vendor_sales = Order::selectRaw('seller_id, SUM(net_amount) as total_amount')
            ->with('seller')->groupBy('seller_id')->get();
        $admin_vendor_sales = $admin_vendor_sales->sortByDesc('orders_count')->values();
        return response()->json(["status" => true, 'admin_vendor_sales' => $admin_vendor_sales], 200);
    }

    public function admin_customer_count()
    {
        $customer_count = User::whereHas('role', function ($query) {
            $query->where('name', 'user');
        })->count();
        $top_customers = Order::selectRaw('user_id, SUM(net_amount) as total_amount')
            ->with('users')->groupBy('user_id')->get();
        $top_customers = $top_customers->sortByDesc('total_amount')->values();
        return response()->json(["status" => true, 'customers_count' => $customer_count, 'top_customers' => $top_customers], 200);
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
        return response()->json(["status" => true, 'lineChart' => $lineChart], 200);
    }

    public function featureProduct()
    {
        $product = Product::with('user')->where('featured', 'Featured')->get();
        return response()->json(["status" => true, 'feature_products' => $product], 200);
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
        return response()->json(["status" => true, 'feature_products' => $product], 200);
    }

    public function likeProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $likeExist = LikeProduct::where('user_id', auth()->user()->id)->where('product_id', $request->product_id)->first();
        if (is_object($likeExist)) {
            if ($likeExist->delete()) return response()->json(['status' => true, 'Message' => "UnLike Successfully"], 200);
            return response()->json(['status' => false, 'Message' => "UnLike not Successfull"]);
        }
        $like = new LikeProduct();
        $like->user_id = auth()->user()->id;
        $like->product_id = $request->product_id;
        if ($like->save()) return response()->json(['status' => true, 'Message' => "Like Successfully"], 200);
        return response()->json(['status' => false, 'Message' => "Like not Successfull"]);
    }

    public function reviewProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_id' => 'required',
            'stars' => 'required',
            'comments' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $review = new ProductReview();
        $review->user_id = auth()->user()->id;
        $review->product_id = $request->product_id;
        $review->stars = $request->stars;
        $review->comments = $request->comments;
        if ($review->save()) return response()->json(['status' => true, 'Message' => "Review Successfully"], 200);
        return response()->json(['status' => false, 'Message' => "Review not Successfull"]);
    }
}
