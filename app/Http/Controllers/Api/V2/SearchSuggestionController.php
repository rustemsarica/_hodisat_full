<?php

namespace App\Http\Controllers\Api\V2;


use App\Models\Search;
use App\Models\Product;
use App\Models\Brand;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;

class SearchSuggestionController extends Controller
{
    public function getList(Request $request)
    {
        $query_key = $request->query_key;
        $type = $request->type;
        $searches;
        if(substr($query_key, 0, 1)=='@'){
            $type="sellers";
        }
        $case1 = $query_key . '%';
        $case2 = '%' . $query_key . '%';
        $brands = [];
        $products = [];
        $shops = [];
        $categories = [];


        if($query_key == ""){
            $search_query = Search::select('id', 'query', 'count');
            $searches = $search_query->orderBy('count', 'desc')->limit(10)->get();
        }


        if ($type == "product") {
            if ($query_key != "") {
                $product_query = Product::query();
                $product_query->where(function ($query) use ($query_key) {
                    foreach (explode(' ', trim($query_key)) as $word) {
                        $query->where('name', 'like', '%'.$word.'%')->orWhere('description', 'like', '%'.$word.'%');
                    }
                });

                $product_query->orderByRaw("CASE
                    WHEN name LIKE '$case1' THEN 1
                    WHEN name LIKE '$case2' THEN 2
                    ELSE 3
                    END");
                $products = filter_products($product_query)->limit(10)->get();

                $brand_query = Brand::query();
                $brand_query->where('name', 'like', "%$query_key%");
                $brand_query->orderByRaw("CASE
                    WHEN name LIKE '$case1' THEN 1
                    WHEN name LIKE '$case2' THEN 2
                    ELSE 3
                    END");
                $brands = $brand_query->limit(10)->get();

                $category_query = Category::query();
                $category_query->where('name', 'like', "%$query_key%");
                $category_query->orderByRaw("CASE
                    WHEN name LIKE '$case1' THEN 1
                    WHEN name LIKE '$case2' THEN 2
                    ELSE 3
                    END");
                $categories = $category_query->limit(10)->get();
            }

        }

        else if ($type == "sellers") {
            $user_query = User::query();
            if ($query_key != "") {
                $query_key=ltrim($query_key,"@");
                $case1 = $query_key . '%';
                $case2 = '%' . $query_key . '%';
                $user_query->where('username', 'like', "$query_key%");
                $user_query->orderByRaw("CASE
                    WHEN name LIKE '$case1' THEN 1
                    WHEN name LIKE '$case2' THEN 2
                    ELSE 3
                    END");
            }

            $users = $user_query->limit(10)->get();
        }



        $items = [];

        //shop push
        if ($type == "sellers" &&  !empty($users)) {
            foreach ($users as  $user) {
                $item = [];
                $item['id'] = $user->shop->id;
                $item['image'] = uploaded_asset($user->shop->logo);
                $item['query'] = $user->username;
                $item['count'] = 0;
                $item['type'] = "shop";
                $item['type_string'] = translate("Shop");

                $items[] = $item;
            }
        }
        else{
            //category push
            if (!empty($categories)) {
                foreach ($categories as  $category) {
                    $item = [];
                    $item['id'] = $category->id;
                    $item['image'] = null;
                    $item['query'] = $category->name;
                    $item['count'] = round(Product::where('category_id',$category->id)->count()/Product::count());
                    $item['type'] = "category";
                    $item['type_string'] = translate("Category");

                    $items[] = $item;
                }
            }

            //brand push
            if (!empty($brands)) {
                foreach ($brands as  $brand) {
                    $item = [];
                    $item['id'] = $brand->id;
                    $item['image'] = null;
                    $item['query'] = $brand->name;
                    $item['count'] = round(Product::where('brand_id',$brand->id)->count()/Product::count());
                    $item['type'] = "brand";
                    $item['type_string'] = translate("Brand");

                    $items[] = $item;
                }
            }

            //product push
            if (!empty($products)) {
                foreach ($products as  $product) {
                    $item = [];
                    $item['id'] = $product->id;
                    $item['image'] = uploaded_asset($product->thumbnail_img);
                    $item['query'] = $product->name;
                    $item['count'] = 0;
                    $item['type'] = "product";
                    $item['type_string'] = translate("Product");

                    $items[] = $item;
                }
            }
        }



        //search push
        if (!empty($searches)) {
            foreach ($searches as  $search) {
                $item = [];
                $item['id'] = $search->id;
                $item['query'] = $search->query;
                $item['count'] = intval($search->count);
                $item['type'] = "search";
                $item['type_string'] = "Search";

                $items[] = $item;
            }
        }

        return $items; // should return a valid json of search list;
    }
}
