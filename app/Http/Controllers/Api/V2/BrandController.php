<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\BrandCollection;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Utility\SearchUtility;
use Cache;
use Illuminate\Support\Facades\DB;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        if($request->name != "" || $request->name != null){
            $brand_query=Brand::where('name', 'like', '%'.$request->name.'%');
            SearchUtility::store($request->name);
			return new BrandCollection($brand_query->paginate(20));
        }else{

			return Cache::remember('app.brands_page-'.$request->page, 86400, function(){
				return new BrandCollection(Brand::orderBy('name')->paginate(100));
			});
		}
        //return new BrandCollection($brand_query->paginate(50));
    }

    public function top(Request $request)
    {
        if($request->category_id>0){
            $ids=DB::table('products')->where('category_id',$request->category_id)->select('brand_id',DB::raw('COUNT(brand_id) AS magnitude'))->groupBy('brand_id')->orderBy('magnitude', 'DESC')->limit(10)->pluck('brand_id')->toArray();
            return new BrandCollection(Brand::whereIn('id', $ids)->get());
        }
        return Cache::remember('app.top_brands', 86400, function(){
            $ids=DB::table('products')->select('brand_id',DB::raw('COUNT(brand_id) AS magnitude'))->groupBy('brand_id')->orderBy('magnitude', 'DESC')->limit(10)->pluck('brand_id')->toArray();
            return new BrandCollection(Brand::whereIn('id', $ids)->get());
        });
    }
}
