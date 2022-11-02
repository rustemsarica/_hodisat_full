<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;


class ProductCollection extends ResourceCollection
{
    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function($data) {

                return [
                    'id' => $data->id,
                    'name' => $data->name,
                    'photos' => explode(',', $data->photos),
                    'thumbnail_image' => static_asset($data->thumbnail!=null ? $data->thumbnail->file_name : 'assets/img/placeholder.jpg'),
                    'base_price' => (integer) home_base_price($data->unit_price, false),
                    'base_discounted_price' => (integer) home_discounted_base_price($data, false),
                    'todays_deal' => (integer) $data->todays_deal,
                    'featured' =>(integer) $data->featured,
                    'discount' => (double) $data->discount,
                    'discount_type' => $data->discount_type,
                    'rating' => (double) $data->rating,
                    'links' => [
                        'details' => "",
                        'reviews' => "",
                        'related' => "",
                        'top_from_seller' => ""
                    ]
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
