<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;
use \App\Models\Product;
use \App\Models\Follow;
use Illuminate\Support\Facades\DB;

class ShopDetailsCollection extends JsonResource
{
    public function toArray($request)
    {
        $blocked=false;
        $followed=false;

        if(auth('sanctum')->check()){
            $user=DB::table('blocked_users')->where(['user_id'=> auth('sanctum')->user()->id, 'blocked_user' => $this->user_id])->exists();
            $follow=Follow::where(['user_id'=> auth('sanctum')->user()->id, 'followed_user_id' => $this->user_id])->exists();

			if($user){
            	$blocked=true;
			}
            if($follow){
                $followed = true;
            }
        }

        return
        [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            "slug" => $this->username,
            'description' => $this->meta_description,
            'logo' => uploaded_asset($this->logo),
            'upload_id' => $this->logo,
            'sliders' => uploaded_asset($this->sliders),
            'sliders_id' => $this->sliders,
            'address' => $this->address,
            'admin_to_pay' => format_price( $this->balance),
            'phone' => $this->phone,

            "following_count" =>Follow::where('user_id',$this->user_id)->count(),
            "follower_count" =>Follow::where('followed_user_id',$this->user_id)->count(),

            'bank_name' => $this->bank_name,
            'bank_acc_name' => $this->bank_acc_name,

            "apply_discount" => $this->apply_discount,
            "min_product_count" => $this->min_product_count,
            "discount_percentage" => $this->discount_percentage,

            'rating' => (double) $this->rating,
            'email'=> $this->email,
            'products'=> 0,
            'orders'=>0,
            //'orders'=> $this->user->seller_orders()->where("delivery_status","delivered")->count(),
            'sales'=>0,
            // 'sales'=>format_price( $this->user->seller_sales()->where("payment_status","paid")->sum('price'),true),

            'is_blocked'=>$blocked,
            'is_followed'=>$followed
        ];

    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }


}
