<?php

namespace App\Services;

use App\Models\Color;
use App\Models\Product;
use App\Models\User;
use App\Utility\ProductUtility;
use Combinations;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class ProductService
{
    public function store(array $data)
    {
        $collection = collect($data);

        $approved = 1;
        if (auth()->user()->user_type == 'seller') {
            $user_id = auth()->user()->id;
            if (get_setting('product_approve_by_admin') == 1) {
                $approved = 0;
            }
        } else {
            $user_id = User::where('user_type', 'admin')->first()->id;
        }

        $discount_start_date = null;
        $discount_end_date   = null;
        if ($collection['date_range'] != null) {
            $date_var               = explode(" to ", $collection['date_range']);
            $discount_start_date = strtotime($date_var[0]);
            $discount_end_date   = strtotime($date_var[1]);
        }
        unset($collection['date_range']);
        $photos=explode(",",$collection['photos']);

        $collection['thumbnail_img'] = $photos[0];


        $shipping_cost = 0;
        if (isset($collection['shipping_type'])) {
            if ($collection['shipping_type'] == 'free') {
                $shipping_cost = 0;
            } elseif ($collection['shipping_type'] == 'flat_rate') {
                $shipping_cost = $collection['flat_shipping_cost'];
            }
        }
        unset($collection['flat_shipping_cost']);
        $slug = Str::slug($collection['name'], '-');
        $same_slug_count = Product::where('slug', 'like', $slug.'%')->count();
        $slug_suffix = $same_slug_count>0 ? '-' . $same_slug_count + 1 : '';
        $slug .= $slug_suffix;

        $colors = $collection['colors'];

        $options = ProductUtility::get_attribute_options($collection);

        $combinations = Combinations::makeCombinations($options);
        if (count($combinations[0]) > 0) {
            foreach ($combinations as $key => $combination) {
                $str = ProductUtility::get_combination_string($combination, $collection);

                unset($collection['price_' . str_replace('.', '_', $str)]);
                unset($collection['sku_' . str_replace('.', '_', $str)]);
                unset($collection['qty_' . str_replace('.', '_', $str)]);
                unset($collection['img_' . str_replace('.', '_', $str)]);
            }
        }

        $choice_options = array();
        if (isset($collection['choice_no']) && $collection['choice_no']) {
            $str = '';
            $item = array();
            foreach ($collection['choice_no'] as $key => $no) {
                $str = 'choice_options_' . $no;
                $item['attribute_id'] = $no;
                $attribute_data = array();
                // foreach (json_decode($request[$str][0]) as $key => $eachValue) {
                foreach ($collection[$str] as $key => $eachValue) {
                    // array_push($data, $eachValue->value);
                    array_push($attribute_data, $eachValue);
                }
                unset($collection[$str]);

                $item['values'] = $attribute_data;
                array_push($choice_options, $item);
            }
        }

        $choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);

        if (isset($collection['choice_no']) && $collection['choice_no']) {
            $attributes = json_encode($collection['choice_no']);
            unset($collection['choice_no']);
        } else {
            $attributes = json_encode(array());
        }

        $published = 1;
        if ($collection['button'] == 'unpublish' || $collection['button'] == 'draft') {
            $published = 0;
        }
        unset($collection['button']);

        $array = $request->category_ids;
        $array = Arr::whereNotNull($array);
        $collection['category_id'] = Arr::last($array);

        $data = $collection->merge(compact(
            'user_id',
            'approved',
            'discount_start_date',
            'discount_end_date',
            'shipping_cost',
            'slug',
            'colors',
            'choice_options',
            'attributes',
            'published',
        ))->toArray();

        return Product::create($data);
    }

    public function update(array $data, Product $product)
    {
        $collection = collect($data);

        $slug = Str::slug($collection['name']);
        $same_slug_count = Product::where('slug', $slug)->where('id','!=',$collection['id'])->count();
        $slug_suffix = $same_slug_count > 1 ? '-' . $same_slug_count + 1 : '';
        $slug .= $slug_suffix;

        if(addon_is_activated('refund_request') && !isset($collection['refundable'])){
            $collection['refundable'] = 0;
        }


        if(!isset($collection['featured'])){
            $collection['featured'] = 0;
        }
        if(!isset($collection['todays_deal'])){
            $collection['todays_deal'] = 0;
        }



        $discount_start_date = null;
        $discount_end_date   = null;
        if ($collection['date_range'] != null) {
            $date_var               = explode(" to ", $collection['date_range']);
            $discount_start_date = strtotime($date_var[0]);
            $discount_end_date   = strtotime($date_var[1]);
        }
        unset($collection['date_range']);

        $photos=explode(",",$collection['photos']);

        $collection['meta_img'] = $photos[0];

        $collection['thumbnail_img'] = $photos[0];


        $shipping_cost = 0;
        if (isset($collection['shipping_type'])) {
            if ($collection['shipping_type'] == 'free') {
                $shipping_cost = 0;
            } elseif ($collection['shipping_type'] == 'flat_rate') {
                $shipping_cost = $collection['flat_shipping_cost'];
            }
        }
        unset($collection['flat_shipping_cost']);

        $colors = $collection['colors'];


        $options = ProductUtility::get_attribute_options($collection);

        $combinations = Combinations::makeCombinations($options);
        if (count($combinations[0]) > 0) {
            foreach ($combinations as $key => $combination) {
                $str = ProductUtility::get_combination_string($combination, $collection);

                unset($collection['price_' . str_replace('.', '_', $str)]);
                unset($collection['sku_' . str_replace('.', '_', $str)]);
                unset($collection['qty_' . str_replace('.', '_', $str)]);
                unset($collection['img_' . str_replace('.', '_', $str)]);
            }
        }


        $choice_options = array();
        if (isset($collection['choice_no']) && $collection['choice_no']) {
            $str = '';
            $item = array();
            foreach ($collection['choice_no'] as $key => $no) {
                $str = 'choice_options_' . $no;
                $item['attribute_id'] = $no;
                $attribute_data = array();
                // foreach (json_decode($request[$str][0]) as $key => $eachValue) {
                foreach ($collection[$str] as $key => $eachValue) {
                    // array_push($data, $eachValue->value);
                    array_push($attribute_data, $eachValue);
                }
                unset($collection[$str]);

                $item['values'] = $attribute_data;
                array_push($choice_options, $item);
            }
        }

        $choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);

        if (isset($collection['choice_no']) && $collection['choice_no']) {
            $attributes = json_encode($collection['choice_no']);
            unset($collection['choice_no']);
        } else {
            $attributes = json_encode(array());
        }

        unset($collection['button']);

        $data = $collection->merge(compact(
            'discount_start_date',
            'discount_end_date',
            'shipping_cost',
            'slug',
            'colors',
            'choice_options',
            'attributes',
        ))->toArray();

        $product->update($data);

        return $product;
    }
}
