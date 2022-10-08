<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

use App\Models\Shop;
use App\Models\Wishlist;

class WishlistCollection extends ResourceCollection
{
    public function toArray($request)
    {
        if(auth()->user()){
            return [
            'data' => $this->collection->map(function($data) {
                $seller = Shop::where('user_id',$data->product->user_id)->first();
                if($seller->logo!=null || $seller->logo != ''){

                    $seller_logo=uploaded_asset($seller->logo);
                    if($seller_logo==null || $seller_logo==''){
                        $seller_logo='https://hodisat.com/public/assets/img/avatar-place.png'; 
                    }
                }else{
                   $seller_logo='https://hodisat.com/public/assets/img/avatar-place.png'; 
                }
                
                $is_in_wishlist=false;
                    $wishlists = Wishlist::where('user_id', auth()->user()->id)->where('product_id',$data->product->id)->get();
                    if($wishlists->count()>0){
                        $is_in_wishlist=true;
                    }
                 
                return [
                    'id' => (integer) $data->id,
                    'product' => [
                        'id' => $data->product->id,
                        'name' => $data->product->name,
                        'thumbnail_image' => uploaded_asset($data->product->thumbnail_img),
                        'base_price' => format_price($data->product->unit_price) ,
                        'rating' => (double) $data->product->rating,                        
                        'seller_id' => $seller->id,
                        'seller_name'=> $seller->name,
                        'seller_avatar' =>  $seller_logo,
                        'is_in_wishlist'=> $is_in_wishlist,
                        'current_stock' => $data->product->current_stock,
                    ]
                ];
            })
        ];
        }else{
            return [
            'data' => $this->collection->map(function($data) {
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
                    'product' => [
                        'id' => $data->product->id,
                        'name' => $data->product->name,
                        'thumbnail_image' => uploaded_asset($data->product->thumbnail_img),
                        'base_price' => format_price(home_base_price($data->product->unit_price, false)) ,
                        'rating' => (double) $data->product->rating,                        
                        'seller_id' => $seller->id,
                        'seller_name'=> $seller->name,
                        'seller_avatar' =>  $seller_logo,
                        'is_in_wishlist'=> false,
                        'current_stock' => $data->product->current_stock
                    ]
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
