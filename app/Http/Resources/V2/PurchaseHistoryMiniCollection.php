<?php

namespace App\Http\Resources\V2;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseHistoryMiniCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'code' => $data->code,
                    'user_id' => intval($data->user_id),
                    'payment_type' => ucwords(str_replace('_', ' ', $data->payment_type)) ,
                    'payment_status' => $data->payment_status,
                    'payment_status_string' => translate(ucwords(str_replace('_', ' ', $data->payment_status))),
                    'delivery_status' => $data->delivery_status,
                    'delivery_status_string' =>  translate(ucwords(str_replace('_', ' ',  $data->delivery_status))),
                    'grand_total' => format_price($data->grand_total) ,
                    'date' => date('d.m.Y', strtotime($data->created_at)),
                    'links' => [
                        'details' => ''
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
