<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\ProductCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Http\Resources\V2\ProductCardCollection;
use App\Http\Resources\V2\ProductDetailCollection;
use App\Http\Resources\V2\FlashDealCollection;
use App\Models\FlashDeal;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Color;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Utility\CategoryUtility;
use App\Utility\SearchUtility;
use Cache;

class ProductController extends Controller
{
    public function index()
    {
        return new ProductMiniCollection(Product::latest()->paginate(20));
    }

    public function show($id)
    {
        $product = Product::withCount('wishlists')->where('id', $id)->get();
        if($product == null || count($product)==0){
            return response()->json([
                'success' => false,
                'status' => 200,
                'data' => []
            ]);
        }
        return new ProductDetailCollection(Product::withCount('wishlists')->where('id', $id)->get());
    }

    public function admin()
    {
        return new ProductCollection(Product::where('added_by', 'admin')->latest()->paginate(10));
    }

    public function seller($id, Request $request)
    {
        $shop = Shop::findOrFail($id);
        $products = Product::where('added_by', 'seller')->where('user_id', $shop->user_id);
        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        $products->where('published', 1)->where('current_stock','>',0);
        return new ProductMiniCollection($products->latest()->paginate(20));
    }

    public function sellerSold($id)
    {
        $shop = Shop::findOrFail($id);
        $orders=Order::where(['seller_id'=>$shop->user_id, 'delivery_status'=>'confirmed'])->pluck('id')->toArray();
        $orderedP=OrderDetail::whereIn('order_id',$orders)->pluck('product_id')->toArray();
        $products = Product::whereIn('id', $orderedP);

        return new ProductMiniCollection($products->latest()->paginate(10));
    }

    public function category($id, Request $request)
    {
        $category_ids = CategoryUtility::children_ids($id);
        $category_ids[] = $id;

        $products = Product::whereIn('category_id', $category_ids);

        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        $products->where('published', 1);
        return new ProductMiniCollection(filter_products($products)->latest()->paginate(20));
    }


    public function brand($id, Request $request)
    {
        $colors = [];
        $attributes = [];

        $products = Product::where('brand_id', $id);
        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
            SearchUtility::store($request->name);
        }

        if ($request->colors != null && $request->colors != "") {
            $colors = Color::whereIn('id',explode(',', $request->colors))->pluck('code')->toArray();
        }

        $sort_by = $request->sort_key;
        $name = $request->name;
        $min = (int)$request->min;
        $max = (int)$request->max;

        if($request->attrs != null && $request->attrs != ""){
            $attributes = explode(',', $request->attrs);
        }
        if (!empty($colors)) {
            $products->whereIn('colors', $colors);
            $products->whereNotNull('colors');
        }

        if(!empty($attributes)){
            $products->where(function ($query) use($attributes) {
                foreach ($attributes as $value) {
                    $str = '"' . $value . '"';
                    $query->orWhere('choice_options', 'like', '%' . $str . '%');
                }
            });
        }

        if ($min != null && $min != "" && is_numeric($min)) {
            $products->where('unit_price', '>=', $min);
        }

        if ($max != null && $max != "" && is_numeric($max)) {
            $products->where('unit_price', '<=', $max);
        }

        switch ($sort_by) {
            case 'price_low_to_high':
                $products->orderBy('unit_price', 'asc');
                break;

            case 'price_high_to_low':
                $products->orderBy('unit_price', 'desc');
                break;

            case 'new_arrival':
                $products->orderBy('created_at', 'desc');
                break;

            case 'popularity':
                $products->orderBy('views', 'desc');
                break;

            case 'top_rated':
                $products->orderBy('rating', 'desc');
                break;

            default:
                $products->inRandomOrder();
                break;
        }

        return new ProductMiniCollection(filter_products($products)->paginate(50));
    }

    public function todaysDeal()
    {
        return Cache::remember('app.todays_deal', 86400, function(){
            $products = Product::where('todays_deal', 1);
            return new ProductMiniCollection(filter_products($products)->limit(20)->latest()->get());
        });
    }

    public function flashDeal()
    {
        return Cache::remember('app.flash_deals', 86400, function(){
            $flash_deals = FlashDeal::where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
            return new FlashDealCollection($flash_deals);
        });
    }

    public function featured()
    {
        $products = Product::where('featured', 1);
        return new ProductMiniCollection(filter_products($products)->latest()->paginate(20));
    }

    public function bestSeller()
    {
        return Cache::remember('app.best_selling_products', 86400, function(){
            $products = Product::query();
            return new ProductMiniCollection(filter_products($products)->limit(20)->get());
        });
    }

    public function related($id)
    {
        return Cache::remember("app.related_products-$id", 86400, function() use ($id){
            $product = Product::find($id);
            $products = Product::where('category_id', $product->category_id)->where('id', '!=', $id);
            return new ProductMiniCollection(filter_products($products)->limit(10)->get());
        });
    }

    public function topFromSeller($id)
    {
        return Cache::remember("app.top_from_this_seller_products-$id", 86400, function() use ($id){
            $product = Product::find($id);
            $products = Product::where('user_id', $product->user_id)->orderBy('views', 'desc');
            return new ProductMiniCollection(filter_products($products)->limit(20)->get());
        });
    }


    public function search(Request $request)
    {

        if($request->categories == "" && $request->brands == "" && $request->colors == "" && $request->attrs == "" && $request->min == "" && $request->max == "" && $request->name == ""){

            $products = Product::withOut(['user','thumbnail'])->where('published',1)
            ->join('users', 'users.id', '=', 'products.user_id')
            ->join('uploads', 'uploads.id', '=', 'products.thumbnail_img')
            ->join('shops', 'products.user_id', '=', 'shops.user_id')
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->select('products.*', 'users.username', 'uploads.file_name', 'shops.id as shop_id', 'shops.logo', 'brands.name as brand_name')->inRandomOrder()->paginate(50);

            return new ProductCardCollection($products);
        }


        $products = Product::withOut(['user','thumbnail'])->where('published',1)
            ->join('users', 'users.id', '=', 'products.user_id')
            ->join('uploads', 'uploads.id', '=', 'products.thumbnail_img')
            ->join('shops', 'products.user_id', '=', 'shops.user_id')
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->select('products.*', 'users.username', 'uploads.file_name', 'shops.id as shop_id', 'shops.logo', 'brands.name as brand_name');
        //return $products = DB::table('products')->join('users', 'users.id', '=', 'products.user_id')->join('uploads', 'uploads.id', '=', 'products.thumbnail_img')->select('products.*', 'users.username', 'uploads.file_name')->paginate(50);

        $category_ids = [];
        $brand_ids = [];
        $colors = [];
        $attributes = [];

        if ($request->categories != null && $request->categories != "") {
            $category_ids = CategoryUtility::children_ids($request->categories);
            $category_ids[] = $request->categories;

            if (!empty($category_ids)) {
                $products->whereIn('category_id', $category_ids);
            }
        }

        if ($request->brands != null && $request->brands != "") {
            $brand_ids = explode(',', $request->brands);

            if (!empty($brand_ids)) {
                $products->whereIn('brand_id', $brand_ids);
            }
        }

        if ($request->colors != null && $request->colors != "") {
            $colors = Color::whereIn('id',explode(',', $request->colors))->pluck('code')->toArray();

            if (!empty($colors)) {
                $products->whereIn('colors', $colors);
                $products->whereNotNull('colors');
            }
        }

        $sort_by = $request->sort_key;
        $name = $request->name;
        $min = (int)$request->min;
        $max = (int)$request->max;

        if($request->attrs != null && $request->attrs != ""){
            $attributes = explode(',', $request->attrs);

            if(!empty($attributes)){
                $products->where(function ($query) use($attributes) {
                    foreach ($attributes as $value) {
                        $str = '"' . $value . '"';
                        $query->orWhere('choice_options', 'like', '%' . $str . '%');
                    }
                });
            }
        }

        if ($name != null && $name != "") {
            $products->where(function ($query) use ($name) {
                foreach (explode(' ', trim($name)) as $word) {
                    $query->where('products.name', 'like', '%'.$word.'%')->orWhere('description', 'like', '%'.$word.'%' );
                }
            });
            SearchUtility::store($name);
        }

        if ($min != null && $min != "" && is_numeric($min)) {
            $products->where('unit_price', '>=', $min);
        }

        if ($max != null && $max != "" && is_numeric($max)) {
            $products->where('unit_price', '<=', $max);
        }

        switch ($sort_by) {
            case 'price_low_to_high':
                $products->orderBy('unit_price', 'asc');
                break;

            case 'price_high_to_low':
                $products->orderBy('unit_price', 'desc');
                break;

            case 'new_arrival':
                $products->orderBy('created_at', 'desc');
                break;

            case 'popularity':
                $products->orderBy('views', 'desc');
                break;

            case 'top_rated':
                $products->orderBy('rating', 'desc');
                break;

            default:
                $products->inRandomOrder();
                break;
        }

        return new ProductCardCollection(filter_products($products)->paginate(50));
    }


    public function home()
    {
        return new ProductCollection(Product::inRandomOrder()->take(50)->get());
    }
}
