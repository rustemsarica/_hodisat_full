<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\CategoryTranslation;
use App\Utility\CategoryUtility;
use App\Models\Attribute;
use App\Models\AttributeCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Cache;
use Illuminate\Support\Facades\DB;
use App;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $categories = Category::where('parent_id', '!=',0)->get();
        // foreach($categories as $category){
        //     $this->create_parent_tree($category->id);
        // }
        // return;
        $sort_search =null;
        $categories = Category::where('parent_id', 0);
        $reorders=DB::table('categories')->select('id')->where('parent_id', 0)->get();
        if ($request->has('search')){
            $sort_search = $request->search;
            $categories = $categories->where('name', 'like', '%'.$sort_search.'%');
        }
        $categories = $categories->orderBy('level','asc')->paginate(20);
        return view('backend.product.categories.index', compact('categories', 'sort_search','reorders'));
    }

    public function subCategories(Request $request, $id)
    {
        $sort_search =null;

        $categories = Category::where('parent_id', $id);
        $reorders=DB::table('categories')->select('id')->where('parent_id', $id)->get();
        $parent = Category::where('id', $id)->first();
        if ($request->has('search')){
            $sort_search = $request->search;
            $categories = $categories->where('name', 'like', '%'.$sort_search.'%');
        }
        $categories = $categories->orderBy('level','asc')->paginate(20);
        return view('backend.product.categories.index', compact('categories', 'sort_search', 'parent','reorders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::where('parent_id', 0)->get();

        return view('backend.product.categories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $category = new Category;
        $category->name = $request->name;
        $category->order_level = 0;
        if($request->order_level != null) {
            $category->order_level = $request->order_level;
        }
        $category->banner = $request->banner;
        $category->icon = $request->icon;
        $category->meta_title = $request->meta_title;
        $category->meta_description = $request->meta_description;

        $array = $request->parent_ids;
        $array = Arr::whereNotNull($array);
        $category->parent_id = Arr::last($array);

        if(collect($array)->implode(',')!=0){
            $category->parent_tree = collect($array)->implode(',');
        }

        if ($category->parent_id != "0") {

            $parent = Category::find($category->parent_id);
            $category->level = $parent->level + 1 ;
        }

        if ($request->slug != null) {
            $category->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        }
        else {
            $category->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name)).'-'.Str::random(5);
        }
        if ($request->commision_rate != null) {
            $category->commision_rate = $request->commision_rate;
        }

        $category->save();

        $category->attributes()->sync($request->filtering_attributes);

        $category_translation = CategoryTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'category_id' => $category->id]);
        $category_translation->name = $request->name;
        $category_translation->save();

        flash(translate('Category has been inserted successfully'))->success();
        return redirect()->route('admin.categories.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $lang = $request->lang;
        $category = Category::findOrFail($id);
        $categories = Category::where('parent_id', 0)
            ->with('childrenCategories')
            ->whereNotIn('id', CategoryUtility::children_ids($category->id, true))->where('id', '!=' , $category->id)
            ->orderBy('name','asc')
            ->get();

        return view('backend.product.categories.edit', compact('category', 'categories', 'lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        if($request->lang == env("DEFAULT_LANGUAGE")){
            $category->name = $request->name;
        }
        if($request->order_level != null) {
            $category->order_level = $request->order_level;
        }
        $category->banner = $request->banner;
        $category->icon = $request->icon;
        $category->meta_title = $request->meta_title;
        $category->meta_description = $request->meta_description;

        $previous_level = $category->level;

        $array = $request->parent_ids;
        $array = Arr::whereNotNull($array);
        $category->parent_id = Arr::last($array);

        if(collect($array)->implode(',')!=0){
            $category->parent_tree = collect($array)->implode(',');
        }else{
            $category->parent_tree ='';
        }

        if ($category->parent_id != "0") {

            $parent = Category::find($category->parent_id);
            $category->level = $parent->level + 1 ;
        }
        else{
            $category->level = 0;
        }

        if($category->level > $previous_level){
            CategoryUtility::move_level_down($category->id);
        }
        elseif ($category->level < $previous_level) {
            CategoryUtility::move_level_up($category->id);
        }

        if ($request->slug != null) {
            $category->slug = strtolower($request->slug);
        }
        else {
            $category->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name)).'-'.Str::random(5);
        }


        if ($request->commision_rate != null) {
            $category->commision_rate = $request->commision_rate;
        }

        $category->save();

        $category->attributes()->sync($request->filtering_attributes);

        $category_translation = CategoryTranslation::firstOrNew(['lang' => $request->lang, 'category_id' => $category->id]);
        $category_translation->name = $request->name;
        $category_translation->save();

        Cache::forget('featured_categories');
        flash(translate('Category has been updated successfully'))->success();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->attributes()->detach();

        // Category Translations Delete
        foreach ($category->category_translations as $key => $category_translation) {
            $category_translation->delete();
        }

        foreach (Product::where('category_id', $category->id)->get() as $product) {
            $product->category_id = null;
            $product->save();
        }

        CategoryUtility::delete_category($id);
        Cache::forget('featured_categories');

        flash(translate('Category has been deleted successfully'))->success();
        return redirect()->route('categories.index');
    }

    public function updateFeatured(Request $request)
    {
        $category = Category::findOrFail($request->id);
        $category->featured = $request->status;
        $category->save();
        Cache::forget('featured_categories');
        return 1;
    }

    public function updateStatus(Request $request)
    {
        Category::whereIn('id',CategoryUtility::children_ids($request->id, true))->orWhere('id',$request->id)->update(['status'=>$request->status]);
        Cache::forget('featured_categories');
        return back();
    }

    public function get_subcategories(Request $request)
    {

        $locale = App::getLocale();
        if($locale=='' || $locale == null){
            $locale = env('DEFAULT_LANGUAGE');
        }
        $categories = DB::table('categories')->where('parent_id',$request->parent_id)->join('category_translations', function ($join) use ($locale) {
            $join->on('categories.id', '=', 'category_translations.category_id')->where('category_translations.lang', $locale);
        })->select('categories.id','category_translations.name','categories.parent_id')->get();

        return json_encode($categories, JSON_UNESCAPED_UNICODE);
    }

    public function getCategoryfields(Request $request)
    {
        $data = array();
        $category=Category::where('id',$request->id)->first();
        $parent_id=$category->parent_id;
        array_push($data, $request->id);
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

        return json_encode(Attribute::whereIn('id', $attributeIds)->get(),JSON_UNESCAPED_UNICODE);


        // $category = DB::table('categories')->where('id',$request->id)->first();
        // $parentIds=explode(',',$category->parent_tree);

        // $attribute_ids = AttributeCategory::whereIn('category_id', $parentIds)->orWhere('category_id', $request->id)->pluck('attribute_id')->toArray();

        //     $attributes = Attribute::whereIn('id', $attribute_ids)->get();
        // return json_encode($attributes, JSON_UNESCAPED_UNICODE);
    }

    public function create_parent_tree($id)
    {
        $data = array();
        $category=Category::where('id',$id)->first();
        $parent_id=$category->parent_id;
        if( $parent_id!=0){
            array_push($data, $parent_id);
        }
        while($parent_id!=0){
            $parcategory=Category::where('id',$parent_id)->first();
            if($parcategory->parent_id>0){
                array_push($data,$parcategory->parent_id);
            }else{
                break;
            }

            $parent_id=$parcategory->parent_id;
        }
        $data = array_reverse($data);
        $category->parent_tree = implode(',', $data);
        $category->save();
    }

    public function categoryReorder(Request $request)
    {
        $categories=json_decode($request->json_categories);
        foreach($categories as $row){
            Category::where('id',$row->id)->update(['level'=>$row->level]);
        }

    }
}
