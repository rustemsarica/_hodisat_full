<?php

namespace App\Http\Controllers\Api\V2;

use App\Services\OrderService;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Product;
use App\Models\OrderDetail;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use App\Models\Wallet;
use \App\Utility\NotificationUtility;
use App\Models\CombinedOrder;
use App\Http\Controllers\AffiliateController;

class OrderController extends Controller
{
    public function store(Request $request, $set_paid = false)
    {
        $cartItems = Cart::where('user_id', $request->user_id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'combined_order_id' => 0,
                'result' => false,
                'message' => translate('Cart is Empty')
            ]);
        }

        $user = User::find($request->user_id);


        $address = Address::where('id', $cartItems->first()->address_id)->first();
        $shippingAddress = [];
        if ($address != null) {
            $shippingAddress['name']        = $user->name;
            $shippingAddress['email']       = $user->email;
            $shippingAddress['address']     = $address->address;
            $shippingAddress['country']     = $address->country->name;
            $shippingAddress['state']       = $address->state->name;
            $shippingAddress['city']        = $address->city->name;
            $shippingAddress['postal_code'] = $address->postal_code;
            $shippingAddress['phone']       = $address->phone;
            if ($address->latitude || $address->longitude) {
                $shippingAddress['lat_lang'] = $address->latitude . ',' . $address->longitude;
            }
        }

        $combined_order = new CombinedOrder;
        $combined_order->user_id = $user->id;
        $combined_order->shipping_address = json_encode($shippingAddress);
        $combined_order->save();

        $seller_products = array();
        foreach ($cartItems as $cartItem) {
            $product_ids = array();
            $product = Product::find($cartItem['product_id']);
            if (isset($seller_products[$product->user_id])) {
                $product_ids = $seller_products[$product->user_id];
            }
            array_push($product_ids, $cartItem);
            $seller_products[$product->user_id] = $product_ids;
        }

        foreach ($seller_products as $seller_product) {
            $order = new Order;
            $order->combined_order_id = $combined_order->id;
            $order->user_id = $user->id;
            $order->shipping_address = json_encode($shippingAddress);
            $order->shipping_type = $cartItems->first()->shipping_type;
            if ($cartItems->first()->shipping_type == 'pickup_point') {
                $order->pickup_point_id = $cartItems->first()->pickup_point;
            }
            $order->payment_type = $request->payment_type;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            $order->code = date('Ymd-His') . rand(10, 99);
            $order->date = strtotime('now');
            if($set_paid){
                $order->payment_status = 'paid';
            }else{
                $order->payment_status = 'unpaid';
            }

            $order->save();

            $subtotal = 0;
            $shipping = 0;
            $coupon_discount = 0;
            $seller_total_price = 0;
            //Order Details Storing
            foreach ($seller_product as $cartItem) {
                $product = Product::find($cartItem['product_id']);

                $subtotal += cart_product_price($cartItem, $product, false) * 1;
                $coupon_discount += $cartItem['discount'];

                $product_variation = $cartItem['variation'];


                if (1 > $product->current_stock) {
                    $order->delete();
                    $combined_order->delete();
                    return response()->json([
                        'combined_order_id' => 0,
                        'result' => false,
                        'message' => translate('The requested quantity is not available for ') . $product->name
                    ]);
                }

                $order_detail = new OrderDetail;
                $order_detail->order_id = $order->id;
                $order_detail->seller_id = $product->user_id;
                $order_detail->product_id = $product->id;
                $order_detail->product_info = json_encode([
                    'id'            =>$product->id,
                    'name'          =>$product->name,
                    'thumbnail_img' =>$product->thumbnail_img,
                ],JSON_UNESCAPED_UNICODE);

                $order_detail->variation = $product_variation;
                $order_detail->price = cart_product_price($cartItem, $product, false) * 1;
                $order_detail->shipping_type = $cartItem['shipping_type'];
                $order_detail->product_referral_code = $cartItem['product_referral_code'];
                $order_detail->shipping_cost = $cartItem['shipping_cost'];
                $order_detail->payment_status = 'paid';
                $shipping += $order_detail->shipping_cost;

                if ($cartItem['shipping_type'] == 'pickup_point') {
                    $order_detail->pickup_point_id = $cartItem['pickup_point'];
                }
                //End of storing shipping cost

                $order_detail->quantity = 1;
                $order_detail->save();
                $seller_total_price+=$order_detail->price;
                $product->save();

                $order->seller_id = $product->user_id;

                if (addon_is_activated('affiliate_system')) {
                    if ($order_detail->product_referral_code) {
                        $referred_by_user = User::where('referral_code', $order_detail->product_referral_code)->first();

                        $affiliateController = new AffiliateController;
                        $affiliateController->processAffiliateStats($referred_by_user->id, 0, $order_detail->quantity, 0, 0);
                    }
                }
            }

            $order->grand_total = $subtotal + $shipping;

            if ($seller_product[0]->coupon_code != null) {
                // if (Session::has('club_point')) {
                //     $order->club_point = Session::get('club_point');
                // }
                $order->coupon_discount = $coupon_discount;
                $order->grand_total -= $coupon_discount;

                $coupon_usage = new CouponUsage;
                $coupon_usage->user_id = $user->id;
                $coupon_usage->coupon_id = Coupon::where('code', $seller_product[0]->coupon_code)->first()->id;
                $coupon_usage->save();
            }

            if(get_setting('vendor_commission_activation')){
                $commission = 0;
                $commission_percentage = get_setting('vendor_commission');
                if(get_setting('vendor_commission_type')== 'percent'){
                    $commission += ($seller_total_price * $commission_percentage)/100;
                }elseif(get_setting('vendor_commission_type')== 'amount'){
                    $commission += $commission_percentage;
                }
                $order->service_cost = $commission;
                $order->grand_total += $commission;
            }

            $combined_order->grand_total += $order->grand_total;
            $order->save();
            if (strpos($request->payment_type, "manual_payment_") !== false) { // if payment type like  manual_payment_1 or  manual_payment_25 etc)

                $order->manual_payment = 1;
                $order->save();

            }


        }
        $combined_order->save();

        Cart::where('user_id', $request->user_id)->delete();

            if ( $request->payment_type == 'wallet')
            {
                (new OrderService)->create_shipping_code($order->id);
                NotificationUtility::sendOrderPlacedNotification($order);

                $wallet = new Wallet;
                $wallet->user_id = $request->user_id;
                $wallet->amount = $order->grand_total;
                $wallet->payment_method = "#".$order->code;
                $wallet->payment_details = $order->code;
                $wallet->approval = 1;
                $wallet->action = "-";
                $wallet->save();

                return response()->json([
                    'combined_order_id' => $combined_order->id,
                    'result' => true,
                    'message' => translate('Your order has been placed successfully')
                ]);
            }

        return $combined_order->id;
        return response()->json([
            'combined_order_id' => $combined_order->id,
            'result' => true,
            'message' => translate('Your order has been placed successfully')
        ]);
    }
}
