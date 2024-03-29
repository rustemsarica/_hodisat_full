<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Product;
use App\Models\Shop;

class NotificationsCollection extends ResourceCollection
{
    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function($data) {

                $images="";
                if($data->item_type=='product' || $data->item_type=='offer'){
                    if(DB::table('products')->where('id',$data->item_type_id)->doesntExist()){
                        return false;
                    }

                    if($data->item_type=='product'){
                       $images = uploaded_asset(Product::where('id',$data->item_type_id)->first()->thumbnail_img);
                    }

                }else if($data->item_type=='user'){

                    if(DB::table('shops')->where('id',$data->item_type_id)->doesntExist()){
                        return false;
                    }

                    $images = uploaded_asset(Shop::where('id',$data->item_type_id)->first()->logo);

                }else if($data->item_type=='order' || $data->item_type=='sell'){
                    if( DB::table('orders')->where('id',$data->item_type_id)->doesntExist()){
                        return false;
                    }
                }

                return [
                    'id' => $data->id,
                    'title' => $data->title,
                    'text' => $data->text,
                    'item_type' => $data->item_type,
                    'item_type_id' => $data->item_type_id,
                   	'receiver_id' =>intval($data->receiver_id),
					'is_read' => $data->is_read,
                    'date' => Carbon::createFromFormat('Y-m-d H:i:s',$data->created_at)->format('F d, Y'),
                    'time' => Carbon::createFromFormat('Y-m-d H:i:s',$data->created_at)->format('h:i a'),

                    'image' => $images
                ];
            })->reject(function ($value) {
                return $value === false;
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
        ];
    }
}
