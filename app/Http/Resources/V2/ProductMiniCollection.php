<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

use App\Models\Shop;
use App\Models\Wishlist;

class ProductMiniCollection extends ResourceCollection
{
    public function toArray($request)
    {

        if(auth('sanctum')->check()){

            return [
                'data' => $this->collection->map(function($data) {

                 $seller = Shop::where('user_id',$data->user_id)->first();
                    if($seller->logo!=null && $seller->logo != ''){

                        $seller_logo=uploaded_asset($seller->logo);
                        if($seller_logo==null || $seller_logo==''){
                            $seller_logo='https://hodisat.com/public/assets/img/avatar-place.png';
                        }
                    }else{
                       $seller_logo='https://hodisat.com/public/assets/img/avatar-place.png';
                    }

                    $is_in_wishlist=false;
                    $wishlists = Wishlist::where('user_id', auth('sanctum')->user()->id)->where('product_id',$data->id)->get();
                    if($wishlists->count()>0){
                        $is_in_wishlist=true;
                    }


                    return [
                        'id' => $data->id,
                        'name' => $data->name,
                        'thumbnail_image' => uploaded_asset($data->thumbnail_img),
                        'has_discount' => $data->unit_price != home_discounted_base_price($data, false) ,
                        'discount'=>"-".discount_in_percentage($data)."%",
                        'stroked_price' => home_base_price($data->unit_price),
                        'main_price' => home_discounted_base_price($data),
                        'rating' => (double) $data->rating,
                        'seller_id' => $seller->id,
                        'seller_name'=> $data->user->username,
                        'seller_avatar' =>  $seller_logo,
                        'is_in_wishlist'=> $is_in_wishlist,
                        'current_stock' => $data->current_stock,
                        'links' => [
                            'details' => route('products.show', $data->id),
                        ],
                        'published' => $data->published
                    ];
                })
            ];
        }else{
            return [
            'data' => $this->collection->map(function($data) {

                $seller = Shop::where('user_id',$data->user_id)->first();
                if($seller->logo!=null && $seller->logo != ''){

                    $seller_logo=uploaded_asset($seller->logo);
                    if($seller_logo==null || $seller_logo==''){
                        $seller_logo='https://hodisat.com/public/assets/img/avatar-place.png';
                    }
                }else{
                   $seller_logo='https://hodisat.com/public/assets/img/avatar-place.png';
                }



                return [
                    'id' => $data->id,
                    'name' => $data->name,
                    'thumbnail_image' => uploaded_asset($data->thumbnail_img),
                    'has_discount' => $data->unit_price != home_discounted_base_price($data, false) ,
                    'discount'=>"-".discount_in_percentage($data)."%",
                    'stroked_price' => home_base_price($data->unit_price),
                    'main_price' => home_discounted_base_price($data),
                    'rating' => (double) $data->rating,
                    'seller_id' => $seller->id,
                    'seller_name'=> $data->user->username,
                    'seller_avatar' =>  $seller_logo,
                    'is_in_wishlist' => false,
                    'current_stock' => $data->current_stock,
                    'links' => [
                        'details' => route('products.show', $data->id),
                    ],
                    'published' => $data->published
                ];
            })
        ];
        }


    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
