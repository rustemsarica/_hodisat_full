<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Review;
use App\Models\Attribute;
use App\Models\Cart;
use App\Models\Wishlist;


class ProductDetailCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {

                $is_in_wishlist=false;
                $is_in_cart=false;
                $wishCount = $data->wishlists_count;
                if(auth('sanctum')->check()){
                    if(Wishlist::where(['user_id'=>auth('sanctum')->user()->id, 'product_id'=>$data->id])->exists()){
                        $is_in_wishlist=true;
                    }
                    if(Cart::where(['user_id'=>auth('sanctum')->user()->id, 'product_id'=>$data->id])->exists()){
                        $is_in_cart=true;
                    }
                }

                $photo_paths = get_images_path($data->photos);

                $photos = [];


                if (!empty($photo_paths)) {
                    for ($i = 0; $i < count($photo_paths); $i++) {
                        if ($photo_paths[$i] != "" ) {
                            $item = array();
                            $item['variant'] = "";
                            $item['path'] = $photo_paths[$i];
                            $photos[]= $item;
                        }
                    }
                }

                $brand = [
                    'id'=> 0,
                    'name'=> "",
                    'logo'=> "",
                ];

                if($data->brand != null) {
                    $brand = [
                        'id'=> $data->brand->id,
                        'name'=> $data->brand->name,
                        'logo'=> uploaded_asset($data->brand->logo),
                    ];
                }


                return [
                    'id' => (integer)$data->id,
                    'name' => $data->name,
                    'added_by' => $data->added_by,
                    'seller_id' => $data->user->id,
                    'seller_vacation_mode' => $data->user->vacation_mode,
                    'shop_id' => $data->added_by == 'admin' ? 0 : $data->user->shop->id,
                    'shop_name' => $data->added_by == 'admin' ? translate('In House Product') : $data->user->username,
                    'shop_logo' => $data->added_by == 'admin' ? uploaded_asset(get_setting('header_logo')) : uploaded_asset($data->user->shop->logo)??"",
                    'photos' => $photos,
                    'string_photos' => $data->photos,
                    'thumbnail_image' => uploaded_asset($data->thumbnail_img),
                    'choice_options' => $this->convertToChoiceOptions(json_decode($data->choice_options)),
                    'colors' => $data->colors,
                    'has_discount' => $data->unit_price != home_discounted_base_price($data, false),
                    'discount'=> "-".discount_in_percentage($data)."%",
                    'stroked_price' => home_base_price($data->unit_price),
                    'main_price' => home_discounted_base_price($data),
                    'calculable_price' => $data->unit_price,
                    'currency_symbol' => currency_symbol(),
                    'current_stock' => $data->current_stock,
                    'earn_point' => (double)$data->earn_point,
                    'description' => $data->description,
                    'brand' => $brand,
                    'link' => route('product', $data->slug),
                    'category_id'=>$data->category_id,
                    'is_in_wishlist'=> $is_in_wishlist,
                    'is_in_cart'=> $is_in_cart,
                    'wish_count' => $wishCount
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

    protected function convertToChoiceOptions($data)
    {
        $result = array();
//        if($data) {
        foreach ($data as $key => $choice) {
            $item['id'] = (int) $choice->attribute_id;
            $item['title'] = Attribute::find($choice->attribute_id)->getTranslation('name');
            $item['options'] = $choice->values;
            array_push($result, $item);
        }
//        }
        return $result;
    }

    protected function convertPhotos($data)
    {
        $result = array();
        foreach ($data as $key => $item) {
            array_push($result, uploaded_asset($item));
        }
        return $result;
    }
}
