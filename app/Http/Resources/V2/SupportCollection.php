<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Support;

class SupportCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $childrens= Support::where('parent_id',$data->id)->count();
                return [
                    'id' => $data->id,
                    'parent_id' => $data->parent_id,
                    'icon' => uploaded_asset($data->icon),
                    'image' => $data->image,
                    'image_url' => $data->image_url,
                    'title' => $data->title,
                    'text' => $data->text,
                    'has_children' => $childrens>0 ? true : false,
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
