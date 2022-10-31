<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Category extends Model
{
    protected $with = ['category_translations'];

    public function getTranslation($field = '', $lang = false){
        $lang = $lang == false ? App::getLocale() : $lang;
        $category_translation = $this->category_translations->where('lang', $lang)->first();
        return $category_translation != null ? $category_translation->$field : $this->$field;
    }

    public function category_translations(){
    	return $this->hasMany(CategoryTranslation::class);
    }

    public function products(){
    	return $this->hasMany(Product::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function childrenCategories()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('categories');
    }

    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class);
    }

    public function subCategoriesIds($id, $container = array())
    {
        $children = DB::table('categories')->select('id')->where('parent_id',$id)->get();

        if (!empty($children)) {
            foreach ($children as $child) {
                $container[] = $child->id;
                $container = $this->subCategoriesIds($child->id, $container);
            }
        }

        return $container;
    }

    public function subCategories($id)
    {
        $children = $this->where('parent_id',$id)->get();

        return $children;
    }
}
