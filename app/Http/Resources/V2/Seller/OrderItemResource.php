<?php

namespace App\Http\Resources\V2\Seller;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'id' => $this->product->id,
            'name' => optional($this->product)->name,
            'thumbnail_image' => uploaded_asset($this->product->thumbnail_img),
            'description' => "desc",
            'delivery_status' => $this->delivery_status,
            'price' => format_price($this->price),
        ];

    
    }
}
