<?php

namespace App\Http\Resources\V2;

use Carbon\Carbon;
use App\Models\Review;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseHistoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                $pickup_point = null;
                if ($data->shipping_type == 'pickup_point' && $data->pickup_point_id) {
                    $pickup_point = $data->pickup_point;
                }

                return [
                    'id' => $data->id,
                    'code' => $data->code,
                    'user_id' => (int) $data->user_id,
                    'seller_id' => (int) $data->seller_id,
                    'payment_type' => ucwords(str_replace('_', ' ', $data->payment_type)),
                    'pickup_point' => $pickup_point,
                    'shipping_type' => $data->shipping_type,
                    'shipping_type_string' => $data->shipping_type != null ? ucwords(str_replace('_', ' ', $data->shipping_type)) : "",
                    'payment_status' => $data->payment_status,
                    'payment_status_string' => translate(ucwords(str_replace('_', ' ', $data->payment_status))),
                    'delivery_status' => $data->delivery_status,
                    'delivery_status_string' => translate(ucwords(str_replace('_', ' ',  $data->delivery_status))),
                    'grand_total' => format_price($data->grand_total),
                    'plane_grand_total' => $data->grand_total,
                    'coupon_discount' => format_price($data->coupon_discount),
                    'shipping_cost' => format_price($data->orderDetails->sum('shipping_cost')),
                    'service_cost' => format_price($data->service_cost),
                    'subtotal' => format_price($data->orderDetails->sum('price')),
                    'date' => date('d.m.Y', strtotime($data->created_at)),
                    'can_cancel' => $data->delivery_status=="pending",
                    'can_review' => Review::where('order_id', $data->id)->count()==0,
                    'manually_payable' => $data->manual_payment && $data->manual_payment_data == null,
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
