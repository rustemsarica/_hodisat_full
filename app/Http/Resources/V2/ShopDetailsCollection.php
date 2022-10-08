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
            $user=DB::table('blocked_users')->where(['user_id'=> auth('sanctum')->user()->id, 'blocked_user' => $this->user_id])->count();
            $follow=Follow::where(['user_id'=> auth('sanctum')->user()->id, 'followed_user_id' => $this->user_id])->count();

			if($user>0){				
            	$blocked=true;
			}
            if($follow>0){
                $followed = true;
            }
        }
        return 
        [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user->name,
            "slug" => $this->slug,
            'description' => $this->meta_description,
            'logo' => uploaded_asset($this->logo),
            'upload_id' => $this->logo,
            'sliders' => get_images_path($this->sliders)[0],
            'sliders_id' => $this->sliders,
            'address' => $this->address,
            'admin_to_pay' => format_price( $this->admin_to_pay),
            'phone' => $this->phone,

            "following_count" =>Follow::where('user_id',$this->user_id)->count(),
            "follower_count" =>Follow::where('followed_user_id',$this->user_id)->count(),

            'bank_name' => $this->user->seller->bank_name,
            'bank_acc_name' => $this->user->seller->bank_acc_name,

            'rating' => (double) $this->rating,
            'email'=> $this->user->email, 
            'products'=> $this->user->products()->count(),
            'orders'=> $this->user->seller_orders()->where("delivery_status","delivered")->count(),
            'sales'=>format_price( $this->user->seller_sales()->where("payment_status","paid")->sum('price'),true),

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

    protected function convertPhotos($data){
        $result = array();
        foreach ($data as $key => $item) {
            array_push($result, uploaded_asset($item));
        }
        return $result;
    }
}
