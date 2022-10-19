<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SupportCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'parent_id' => $data->parent_id,
                    'icon' => $data->icon,
                    'image' => $data->image,
                    'image_url' => $data->image_url,
                    'title' => $data->title,
                    'text' => $data->text,
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
