<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Order;

class NotificationsCollection extends ResourceCollection
{
    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function($data) {

                $images="";
                if($data->item_type=='product' || $data->item_type=='offer'){
                    $product = Product::where('id',$data->item_type_id)->count();
                    if($product==null){
                        return ;
                    }
                    $images = uploaded_asset($product->thumbnail_img);
                }else if($data->item_type=='user'){
                    $shop = Shop::where('id',$data->item_type_id)->count();
                    if($shop == 0){
                        return ;
                    }
                    $images = uploaded_asset($shop->logo);
                }else if($data->item_type=='order' || $data->item_type=='sell'){
                    $order = Order::where('id',$data->item_type_id)->count();
                    if($order == 0){
                        return ;
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
