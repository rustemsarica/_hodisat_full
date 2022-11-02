<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CategoryCollection;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\AttributeCategory;
use App\Models\Attribute;
use Cache;
use Illuminate\Support\Collection;
use App\Utility\CategoryUtility;


class CategoryController extends Controller
{

    public function index($parent_id = 0)
    {
        if(request()->has('parent_id') && is_numeric (request()->get('parent_id'))){
          $parent_id = request()->get('parent_id');
        }

        return Cache::remember("app.categories-$parent_id", 86400, function() use ($parent_id){
            return new CategoryCollection(Category::where('status',1)->where('parent_id', $parent_id)->get());
        });
    }

    public function featured()
    {
        return Cache::remember('app.featured_categories', 86400, function(){
            return new CategoryCollection(Category::where('status',1)->where('featured', 1)->get());
        });
    }

    public function home()
    {
        return Cache::remember('app.home_categories', 86400, function(){
            return new CategoryCollection(Category::where('status',1)->whereIn('id', json_decode(get_setting('home_categories')))->get());
        });
    }

    public function top()
    {
        return Cache::remember('app.top_categories', 86400, function(){
            return new CategoryCollection(Category::where('status',1)->whereIn('id', json_decode(get_setting('home_categories')))->limit(20)->get());
        });
    }

    public function getCategoryAttributes($id){
        $data = array();
        $category=Category::where('id',$id)->first();
        $parent_id=$category->parent_id;
        array_push($data, $id);
        if( $parent_id!=0){
            array_push($data, $parent_id);
        }
        while($parent_id!=0){
            $category=Category::where('id',$parent_id)->first();
            if($category->parent_id>0){
                array_push($data,$category->parent_id);
            }else{
                break;
            }

            $parent_id=$category->parent_id;
        }

        $attributeIds=array();
        foreach($data as $item){
            if($attributeIds==null){
                $id_array=AttributeCategory::where('category_id', $item)->pluck('attribute_id')->toArray();
                if($id_array!=null){
                    $attributeIds = $id_array;
                    break;
                }
            }
        }


        return [
            'data'=>new Collection(Attribute::whereIn('id', $attributeIds)->with('attribute_values')->get()),
            'success' => true,
            'status' => 200
            ];

    }

    public function getCategory($id){
        $data=Category::where('status',1)->where('id', $id)->first();

        $banner ='';
        if(uploaded_asset($data->banner)) {
            $banner = uploaded_asset($data->banner);
        }
        $icon ='';
        if(uploaded_asset(uploaded_asset($data->icon))) {
            $icon = uploaded_asset($data->icon);
        }

        return collect([
            'id' => $data->id,
            'name' => $data->getTranslation('name'),
            'banner' => $banner,
            'icon' => $icon,
            'number_of_children' => CategoryUtility::get_immediate_children_count($data->id),
            'parent_id' => $data->parent_id,
            'links' => [
                'products' => route('api.products.category', $data->id),
                'sub_categories' => route('api.subCategories.index', $data->id)
            ]
        ]);
        return new CategoryCollection(Category::where('status',1)->where('id', $id)->get());
    }



    public function categoryParentTree($id)
    {
        $data = array();
        $category=Category::where('status',1)->where('id',$id)->first();
        $parent_id=$category->parent_id;
        array_push($data, $id);
        if( $parent_id!=0){
            array_push($data, $parent_id);
        }
        while($parent_id!=0){
            $category=Category::where('status',1)->where('id',$parent_id)->first();
            if($category->parent_id>0){
                array_push($data,$category->parent_id);
            }else{
                break;
            }

            $parent_id=$category->parent_id;
        }

    }
}
