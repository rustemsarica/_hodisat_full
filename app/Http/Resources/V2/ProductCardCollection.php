<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

use App\Models\Wishlist;

class ProductCardCollection extends ResourceCollection
{
    public function toArray($request)
    {

            return [
                'data' => $this->collection->map(function($data) {

                    $is_in_wishlist=false;

                    if(auth('sanctum')->check()){
                        $wishlists = Wishlist::where('user_id', auth('sanctum')->user()->id)->where('product_id',$data->id)->exists();
                        if($wishlists){
                            $is_in_wishlist=true;
                        }
                    }

                    $has_discount = $data->unit_price != home_discounted_base_price($data, false);

                    return [
                        'id' => $data->id,
                        'name' => $data->name,
                        'thumbnail_image' => static_asset($data->file_name),
                        'has_discount' => $has_discount,
                        'discount'=> $has_discount ? "-".discount_in_percentage($data)."%" : "",
                        'stroked_price' => $has_discount ? home_base_price($data->unit_price) : "",
                        'main_price' => $has_discount ? home_discounted_base_price($data) : home_base_price($data->unit_price),
                        'seller_id' => $data->shop_id,
                        'seller_name'=> $data->username,
                        'seller_avatar' =>  uploaded_asset($data->logo),
                        'is_in_wishlist'=> $is_in_wishlist,
                        'current_stock' => $data->current_stock,
                        'links' => [
                            'details' => ""
                        ],
                        'published' => $data->published,
                        'approved' => $data->approved,
                        'wish_count' => 0,
                        'brand' => $data->brand_name
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
