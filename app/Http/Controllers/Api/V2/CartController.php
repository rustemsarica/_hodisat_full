<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function summary()
    {
        //$user = User::where('id', auth()->user()->id)->first();
        $items = auth()->user()->carts;
        if ($items->isEmpty()) {
            return response()->json([
                'sub_total' => format_price(0.00),
                'shipping_cost' => format_price(0.00),
                'service_cost' => format_price(0.00),
                'discount' => format_price(0.00),
                'grand_total' => format_price(0.00),
                'grand_total_value' => 0.00,
                'coupon_code' => "",
                'coupon_applied' => false,
            ]);
        }

        $sum = 0.00;
        $subtotal = 0.00;
        $commission = 0.00;
        foreach ($items as $cartItem) {
            $item_sum = 0.00;
            $item_sum += $cartItem->price * $cartItem->quantity;
            $item_sum += $cartItem->shipping_cost - $cartItem->discount;
            $sum +=  $item_sum  ;   //// 'grand_total' => $request->g

            $subtotal += $cartItem->price * $cartItem->quantity;
        }

        $bulk_sell_discount=0.00;
        $owner_ids = Cart::where('user_id', auth()->user()->id)->select('owner_id')->groupBy('owner_id')->pluck('owner_id')->toArray();
        if (!empty($owner_ids)) {
            foreach ($owner_ids as $owner_id) {
                $shop_data = Shop::where('user_id', $owner_id)->first();
                $seller_total_price=Cart::where(['user_id'=> auth()->user()->id, 'owner_id' => $owner_id])->sum('price');
                if($shop_data->apply_discount==1){
                    $cart_items_count=Cart::where(['user_id'=> auth()->user()->id, 'owner_id' => $owner_id])->count();
                    if($cart_items_count>=$shop_data->min_product_count){
                        $bulk_sell_discount+=($seller_total_price/100)*$shop_data->discount_percentage;
                    }
                }
                if(get_setting('vendor_commission_activation')){
                    $commission_percentage = get_setting('vendor_commission');
                    if(get_setting('vendor_commission_type')== 'percent'){
                        $commission += ($seller_total_price * $commission_percentage)/100;
                    }elseif(get_setting('vendor_commission_type')== 'amount'){
                        $commission += $commission_percentage;
                    }

                }
            }
        }



        $sum += $commission;

        return response()->json([
            'sub_total' => format_price($subtotal),
            'shipping_cost' => format_price($items->sum('shipping_cost')),
            'service_cost' => format_price($commission),
            'discount' => format_price($items->sum('discount')+$bulk_sell_discount),
            'grand_total' => format_price($sum-$bulk_sell_discount),
            'grand_total_value' => convert_price($sum-$bulk_sell_discount),
            'coupon_code' => $items[0]->coupon_code,
            'coupon_applied' => $items[0]->coupon_applied == 1,
        ]);


    }


    public function count()
    {
        if(auth()->check()){
             $items = Cart::where('user_id',auth()->user()->id)->count();

            return response()->json([
                'count' => $items,
                'status' => true,
            ]);
        }else{
            return response()->json([
                'count' => 0,
                'status' => true,
            ]);
        }

    }



    public function getList()
    {
        $owner_ids = Cart::where('user_id', auth()->user()->id)->select('owner_id')->groupBy('owner_id')->pluck('owner_id')->toArray();
        $currency_symbol = currency_symbol();
        $shops = [];
        if (!empty($owner_ids)) {
            foreach ($owner_ids as $owner_id) {
                $shop = array();
                $shop_items_raw_data = Cart::where('user_id', auth()->user()->id)->where('owner_id', $owner_id)->get()->toArray();
                $shop_items_data = array();
                if (!empty($shop_items_raw_data)) {
                    foreach ($shop_items_raw_data as $shop_items_raw_data_item) {

                        $product = Product::where('id', $shop_items_raw_data_item["product_id"])->first();
                        $price = (double) cart_product_price($shop_items_raw_data_item, $product, false, false);

                        $offer = Offer::where(['product_id'=>$shop_items_raw_data_item["product_id"], 'user_id'=>auth()->user()->id, 'answer'=>1])->first();
                        if($offer!=null){
                            $price = $offer->offer_value;
                        }
                        $shop_items_data_item["id"] = intval($shop_items_raw_data_item["id"]) ;
                        $shop_items_data_item["owner_id"] =intval($shop_items_raw_data_item["owner_id"]) ;
                        $shop_items_data_item["user_id"] =intval($shop_items_raw_data_item["user_id"]) ;
                        $shop_items_data_item["product_id"] =intval($shop_items_raw_data_item["product_id"]) ;
                        $shop_items_data_item["product_name"] = $product->name;
                        $shop_items_data_item["product_thumbnail_image"] = uploaded_asset($product->thumbnail_img);
                        $shop_items_data_item["variation"] = $shop_items_raw_data_item["variation"];
                        $shop_items_data_item["price"] = $price;
                        $shop_items_data_item["currency_symbol"] = $currency_symbol;
                        $shop_items_data_item["shipping_cost"] =(double) $shop_items_raw_data_item["shipping_cost"];
                        $shop_items_data_item["quantity"] =intval($shop_items_raw_data_item["quantity"]) ;
                        $shop_items_data_item["lower_limit"] = intval(1) ;
                        $shop_items_data_item["upper_limit"] = intval(1) ;

                        $shop_items_data[] = $shop_items_data_item;

                    }
                }


                $shop_data = Shop::where('user_id', $owner_id)->first();
                if ($shop_data) {
                    $shop['name'] = $shop_data->user->username;
                    $shop['seller_avatar'] = $shop_data->logo== null ? "https://hodisat.com/public/uploads/avatar-place.png" : uploaded_asset($shop_data->logo);
                    $shop['apply_discount'] = $shop_data->apply_discount;
                    $shop['min_product_count'] = $shop_data->min_product_count;
                    $shop['discount_percentage'] = $shop_data->discount_percentage;
                    $shop['owner_id'] =(int) $shop_data->id;
                    $shop['cart_items'] = $shop_items_data;
                } else {
                    $shop['name'] = "Inhouse";
                    $shop['owner_id'] =(int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;
                }
                $shops[] = $shop;
            }
        }

        //dd($shops);

        return response()->json($shops);
    }


    public function add(Request $request)
    {
        $is_in_cart = Cart::where(['user_id'=>auth()->user()->id, 'product_id'=>$request->id])->count();

        if($is_in_cart>0){
            return response()->json(['result' => false, 'message' => translate("The product is already in cart") ], 200);
        }


        $product = Product::findOrFail($request->id);

        if($product->approved!=1){
            return response()->json(['result' => false, 'message' => translate("Sorry. This product is not ready for sale.") ], 200);
        }

        if($product->user_id==auth()->user()->id){
            return response()->json(['result' => false, 'message' => translate("You cannot buy your own products.") ], 200);
        }
        $variant = $request->variant;


        $price = $product->unit_price;

        $offer = Offer::where(['product_id'=>$request->id, 'user_id'=>auth()->user()->id, 'answer'=>1])->first();

        if($offer!=null){
            $price = $offer->offer_value;
        }

        //discount calculation based on flash deal and regular discount

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        }
        elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'amount'){
                $price -= $product->discount;
            }
        }


        if (1 > $request->quantity) {
            return response()->json(['result' => false, 'message' => translate("Minimum")." {1} ".translate("item(s) should be ordered")], 200);
        }

        $stock = $product->current_stock;

        $variant_string = $variant != null && $variant != "" ? translate("for")." ($variant)" : "";
        if ($stock < $request->quantity) {
            if ($stock == 0) {
                return response()->json(['result' => false, 'message' => "Stock out"], 200);
            } else {
                return response()->json(['result' => false, 'message' => translate("Only") ." {$stock} ".translate("item(s) are available")." {$variant_string}"], 200);
            }
        }

        Cart::updateOrCreate([
            'user_id' => auth()->user()->id,
            'owner_id' => $product->user_id,
            'product_id' => $request->id,
            'variation' => $variant
        ], [
            'price' => $price,
            'shipping_cost' => 0,
            'quantity' => DB::raw("quantity + $request->quantity")
        ]);



        return response()->json([
            'result' => true,
            'message' => translate('Product added to cart successfully')
        ]);
    }


    public function process(Request $request)
    {
        $cart_ids = explode(",", $request->cart_ids);
        $cart_quantities = explode(",", $request->cart_quantities);

        if (!empty($cart_ids)) {
            $i = 0;
            foreach ($cart_ids as $cart_id) {
                $cart_item = Cart::where('id', $cart_id)->first();
                $product = Product::where('id', $cart_item->product_id)->first();

                if (1 > $cart_quantities[$i]) {
                    return response()->json(['result' => false, 'message' => translate("Minimum")." {1} ".translate("item(s) should be ordered for")." {$product->name}"], 200);
                }

                $stock = $cart_item->product->current_stock;
                $variant_string = $cart_item->variation != null && $cart_item->variation != "" ? " ($cart_item->variation)" : "";
                if ($stock >= $cart_quantities[$i]) {
                    $cart_item->update([
                        'quantity' => $cart_quantities[$i]
                    ]);

                } else {
                    if ($stock == 0) {
                        return response()->json(['result' => false, 'message' => translate("No item is available for")." {$product->name}{$variant_string},".translate("remove this from cart")], 200);
                    } else {
                        return response()->json(['result' => false, 'message' => translate("Only")." {$stock} ".translate("item(s) are available for")." {$product->name}{$variant_string}"], 200);
                    }

                }

                $i++;
            }

            return response()->json(['result' => true, 'message' => translate('Cart updated')], 200);

        } else {
            return response()->json(['result' => false, 'message' => translate('Cart is empty')], 200);
        }


    }

    public function destroy($id)
    {
        Cart::destroy($id);
        return response()->json(['result' => true, 'message' => translate('Product is successfully removed from your cart')], 200);
    }

    public function checkProduct(Request $request)
    {
        $is_in_cart = Cart::where(['user_id'=>auth()->user()->id, 'product_id'=>$request->id])->count();

        if($is_in_cart>0){
            return response()->json(['result' => true, 'message' => "Ürün sepette"], 200);
        }
        return response()->json(['result' => false, 'message' => "Ürün sepette değil"], 200);
    }

    public function removeProduct(Request $request)
    {
        Cart::where(['user_id'=>auth()->user()->id, 'product_id'=>$request->id])->delete();

        return response()->json(['result' => true, 'message' => translate('Product is successfully removed from your cart')], 200);
    }
}
