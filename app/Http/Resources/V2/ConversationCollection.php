<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ConversationCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'receiver_id' => intval($data->receiver_id) ,
                    'receiver_type'=> $data->receiver->user_type,
                    'shop_id' => $data->receiver->id == auth()->user()->id ? $data->sender->shop->id : $data->receiver->shop->id  ,
                    'shop_name' => $data->receiver->id == auth()->user()->id ? $data->sender->username : $data->receiver->username,
                    'shop_logo' => $data->receiver->id == auth()->user()->id ? uploaded_asset($data->sender->shop->logo) :  uploaded_asset($data->receiver->shop->logo),
                    'title'=> $data->title,
                    'sender_viewed'=> intval($data->sender_viewed),
                    'receiver_viewed'=> intval($data->receiver_viewed),
                    'date'=> $data->updated_at,
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
