<?php

namespace App\Http\Resources\V2\Seller;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        return [
            'order_code'        => $this->code,
            'total'             => format_price($this->grand_total),
            'order_date'        => date('d.m.Y', strtotime($this->created_at)),
            'payment_status'    => translate($this->payment_status),
            'delivery_status'   => translate($this->delivery_status),
            'shipping_code'     => $this->shipping_code,
            'shipping_cost'     => format_price($this->orderDetails->sum('shipping_cost')),
            'subtotal'          => format_price($this->orderDetails->sum('price')),
            'coupon_discount'   => format_price($this->coupon_discount),
            'order_items'       => OrderItemResource::collection($this->orderDetails)
        ];
    }
}
