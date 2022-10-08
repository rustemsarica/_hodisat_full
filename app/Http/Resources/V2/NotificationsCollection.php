<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        
        return [
            'data' => $this->collection->map(function($data) {
				$str="";
				if($data->type == 'App\Notifications\OrderNotification'){
					$message=JSON_DECODE($data->data);
					$str.=translate('Order: ').$message->order_code.translate(' has been '. ucfirst(str_replace('_', ' ', $message->status)));
				}
                
                return [
                    'id' => (string)$data->id,
                    'type' => $data->type,
                   	'notifiable_id' =>intval($data->notifiable_id),
                    'data' => $str!="" ? $str : $data->data,
					'read_at' => $data->read_at,
                    'year' => Carbon::createFromFormat('Y-m-d H:i:s',$data->created_at)->format('Y'),
                    'month' => Carbon::createFromFormat('Y-m-d H:i:s',$data->created_at)->format('m'),
                    'day_of_month' => Carbon::createFromFormat('Y-m-d H:i:s',$data->created_at)->format('d-M'),
                    'date' => Carbon::createFromFormat('Y-m-d H:i:s',$data->created_at)->format('F d, Y'),
                    'time' => Carbon::createFromFormat('Y-m-d H:i:s',$data->created_at)->format('h:i a'),                   
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
