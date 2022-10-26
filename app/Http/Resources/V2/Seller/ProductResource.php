<?php

namespace App\Http\Resources\V2\Seller;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Review;
use App\Models\Attribute;
use App\Models\Category;
use App\Utility\CategoryUtility;


class ProductResource extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                $precision = 2;
                $photo_paths = get_images_path($data->photos);

                $photos = [];


                if (!empty($photo_paths)) {
                    for ($i = 0; $i < count($photo_paths); $i++) {
                        if ($photo_paths[$i] != "" ) {
                            $item = array();
                            $item['variant'] = "";
                            $item['path'] = $photo_paths[$i];
                            $photos[]= $item;
                        }

                    }

                }


                if($data->brand != null) {
                    $brand = [
                        'id'=> $data->brand->id,
                        'name'=> $data->brand->name,
						'links' => [
                            'products' => route('api.products.brand', $data->brand->id)
                        ]
                    ];
                }

				$category=Category::where('id',$data->category_id)->first();
                $icon ='';
                if(uploaded_asset(uploaded_asset($category->icon))) {
                    $icon = uploaded_asset($category->icon);
                }

                $categoryInfo = [
                    'id' => $category->id,
                    'name' => $category->getTranslation('name'),
                    'banner' => "",
                    'icon' => $icon,
                    'number_of_children' => CategoryUtility::get_immediate_children_count($category->id),
                    'parent_id' => $category->parent_id,
                    'links' => [
                        'products' => "",
                        'sub_categories' => ""
                    ]
                ];

                return [
                    'id' => (integer)$data->id,
                    'name' => $data->name,
                    'added_by' => $data->added_by,
                    'seller_id' => $data->user->id,
                    'photos' => $photos,
                    'choice_options' => $this->convertToChoiceOptions(json_decode($data->choice_options)),
                    'colors' => $data->colors,
                    'has_discount' => home_base_price($data->unit_price, false) != home_discounted_base_price($data, false),
                    'discount'=> "-".discount_in_percentage($data)."%",
                    'calculable_price' => $data->unit_price,
                    'earn_point' => (double)$data->earn_point,
                    'description' => $data->description,
                    'brand' => $brand,
                    'category_id'=>$categoryInfo,
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

    protected function convertToChoiceOptions($data)
    {
        $result = array();
//        if($data) {
        foreach ($data as $key => $choice) {
            $item['id'] = (int) $choice->attribute_id;
            $item['title'] = Attribute::find($choice->attribute_id)->getTranslation('name');
            $item['options'] = $choice->values;
            array_push($result, $item);
        }
//        }
        return $result;
    }

    protected function convertPhotos($data)
    {
        $result = array();
        foreach ($data as $key => $item) {
            array_push($result, uploaded_asset($item));
        }
        return $result;
    }
}
