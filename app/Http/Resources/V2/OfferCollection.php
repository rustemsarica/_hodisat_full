<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

use App\Models\Shop;
use App\Models\User;

class OfferCollection extends ResourceCollection
{
    public function toArray($request)
    {

            return [
            'data' => $this->collection->map(function($data) {
                $user = User::where('id', $data->user_id)->first();
                $seller = Shop::where('user_id',$data->product->user_id)->first();

                if($seller->logo!=null || $seller->logo != ''){
                    $seller_logo=uploaded_asset($seller->logo);
                    if($seller_logo==null || $seller_logo==''){
                        $seller_logo='https://hodisat.com/public/assets/img/avatar-place.png';
                    }
                }else{
                   $seller_logo='https://hodisat.com/public/assets/img/avatar-place.png';
                }

                return [
                    'id' => (integer) $data->id,
                    'shop'=> [
                        'id'=> $user->shop->id,
                        'name' => $user->username,
                        'logo' => uploaded_asset($user->shop->logo),
                        'raiting' => null
                    ],
                    'product' => [
                        'id' => $data->product->id,
                        'name' => $data->product->name,
                        'thumbnail_image' => uploaded_asset($data->product->thumbnail_img),
                        'base_price' => format_price($data->product->unit_price),
                        'seller_id' => $seller->id,
                        'seller_name'=> $seller->product->user->username,
                        'seller_avatar' =>  $seller_logo,
                        'rating' => 0,
                        'is_in_wishlist'=> false,
                        'current_stock' => $data->product->current_stock
                    ],
                    'offer_value' => $data->offer_value,
                    'answer' => $data->answer,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at
                ];
            })
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
