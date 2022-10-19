<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\ProductMiniCollection;
use App\Http\Resources\V2\FollowCollection;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use \App\Utility\NotificationUtility;

class WishlistController extends Controller
{

    public function index($id)
    {
        $shop = Shop::findOrFail($id);
        $product_ids = Wishlist::where('user_id', $shop->user_id)->pluck("product_id")->toArray();
        $products = Product::whereIn('id', $product_ids)->where('current_stock','>',0);

        return new ProductMiniCollection($products->latest()->paginate(10));
    }

    public function store(Request $request)
    {
        Wishlist::updateOrCreate(
            ['user_id' => $request->user_id, 'product_id' => $request->product_id]
        );
        return response()->json(['message' => translate('Product is successfully added to your wishlist')], 201);
    }

    public function destroy($id)
    {
        try {
            Wishlist::destroy($id);
            return response()->json(['result' => true, 'message' => translate('Product is successfully removed from your wishlist')], 200);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => $e->getMessage()], 200);
        }

    }

    public function add(Request $request)
    {
        $product = Wishlist::where(['product_id' => $request->product_id, 'user_id' => auth()->user()->id])->count();
        if ($product > 0) {
            return response()->json([
                'message' => translate('Product present in wishlist'),
                'is_in_wishlist' => true,
                'product_id' => (integer)$request->product_id,
                'wishlist_id' => (integer)Wishlist::where(['product_id' => $request->product_id, 'user_id' => auth()->user()->id])->first()->id
            ], 200);
        } else {
            Wishlist::create(
                ['user_id' =>auth()->user()->id, 'product_id' => $request->product_id]
            );
            $product = Product::where('id', $request->product_id)->first();
            if (get_setting('google_firebase') == 1 && $product->user->device_token != null) {
                if($product->user->notification_permissions!=null && $product->user->notification_permissions->app_follow==0){
                    return response()->json(['result' => true], 200);
                }
                $request->device_token = $product->user->device_token;
                $request->title = "Ürünün dikkat çekiyor";
                $request->text = auth()->user()->username." ürününü beğendi.";

                $request->type = "product";
                $request->id = $product->id;
                $request->user_id = $product->user->id;
                $request->image = uploaded_asset($product->thumbnail_img);

                NotificationUtility::sendFirebaseNotification($request);
            }

            return response()->json([
                'message' => translate('Product added to wishlist'),
                'is_in_wishlist' => true,
                'product_id' => (integer)$request->product_id,
                'wishlist_id' => (integer)Wishlist::where(['product_id' => $request->product_id, 'user_id' => auth()->user()->id])->first()->id
            ], 200);
        }

    }

    public function remove(Request $request)
    {
        $product = Wishlist::where(['product_id' => $request->product_id, 'user_id' =>  auth()->user()->id])->count();
        if ($product == 0) {
            return response()->json([
                'message' => translate('Product in not in wishlist'),
                'is_in_wishlist' => false,
                'product_id' => (integer)$request->product_id,
                'wishlist_id' => 0
            ], 200);
        } else {
            Wishlist::where(['product_id' => $request->product_id, 'user_id' => auth()->user()->id])->delete();

            return response()->json([
                'message' => translate('Product is removed from wishlist'),
                'is_in_wishlist' => false,
                'product_id' => (integer)$request->product_id,
                'wishlist_id' => 0
            ], 200);
        }
    }

    public function isProductInWishlist(Request $request)
    {
        $product = Wishlist::where(['product_id' => $request->product_id, 'user_id' => auth()->user()->id])->count();
        if ($product > 0)
            return response()->json([
                'message' => translate('Product present in wishlist'),
                'is_in_wishlist' => true,
                'product_id' => (integer)$request->product_id,
                'wishlist_id' => (integer)Wishlist::where(['product_id' => $request->product_id, 'user_id' => auth()->user()->id])->first()->id
            ], 200);

        return response()->json([
            'message' => translate('Product is not present in wishlist'),
            'is_in_wishlist' => false,
            'product_id' => (integer)$request->product_id,
            'wishlist_id' => 0
        ], 200);
    }

    public function getProductLikes(Request $request)
    {
        $ids = Wishlist::where('product_id',$request->product_id)->pluck('user_id')->toArray();
        return new FollowCollection(Shop::whereIn('user_id',$ids)->get());
    }
}
