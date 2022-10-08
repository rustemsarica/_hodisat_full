<?php

namespace App\Http\Controllers\Api\V2\Seller;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\V2\ReviewCollection;
use App\Http\Resources\V2\Seller\ProductCollection;
use App\Http\Resources\V2\Seller\ProductResource;
use App\Http\Resources\V2\Seller\ProductReviewCollection;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\ProductTranslation;
use App\Services\ProductService;
use Artisan;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Upload;
use Illuminate\Support\Facades\File;
use Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->where('user_id', auth()->user()->id)->paginate(10);
        return new ProductCollection($products);
    }

    public function edit($id)
    {
        $product = Product::where('id',$id)->get();
        return new ProductResource($product);
    }

    public function change_status(Request $request)
    {
        $product = Product::where('user_id', auth()->user()->id)
            ->where('id', $request->id)
            ->update([
                'published' => $request->status
            ]);

        if ($product == 0) {
            return $this->failed(translate('This product is not yours'));
        }
        return ($request->status == 1) ?
            $this->success(translate('Product has been published successfully')) :
            $this->success(translate('Product has been unpublished successfully'));
    }

    public function change_featured_status(Request $request)
    {
        $product = Product::where('user_id', auth()->user()->id)
            ->where('id', $request->id)
            ->update([
                'seller_featured' => $request->featured_status
            ]);

        if ($product == 0) {
          return  $this->failed(translate('This product is not yours'));
        }

        return ($request->featured_status == 1) ?
            $this->success(translate('Product has been featured successfully')) :
            $this->success(translate('Product has been unfeatured successfully'));
    }

    public function duplicate($id)
    {
        $product = Product::findOrFail($id);
        
        if (auth()->user()->id != $product->user_id) {
            return $this->failed(translate('This product is not yours'));
        }
        if (addon_is_activated('seller_subscription')) {
            if (!seller_package_validity_check(auth()->user()->id)) {
                return $this->failed(translate('Please upgrade your package'));
            }
        }

        if (auth()->user()->id == $product->user_id) {
            $product_new = $product->replicate();
            $product_new->slug = $product_new->slug . '-' . Str::random(5);
            $product_new->save();

            return $this->success(translate('Product has been duplicated successfully'));
        }
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if (auth()->user()->id != $product->user_id) {
            return $this->failed(translate('This product is not yours'));
        }

        $product->product_translations()->delete();

        if (Product::destroy($id)) {
            Cart::where('product_id', $id)->delete();

            return $this->success(translate('Product has been deleted successfully'));

            Artisan::call('view:clear');
            Artisan::call('cache:clear');
        }
    }

    public function product_reviews()
    {
        $reviews = Review::orderBy('id', 'desc')
            ->join('products', 'reviews.product_id', '=', 'products.id')
            ->join('users','reviews.user_id','=','users.id')
            ->where('products.user_id', auth()->user()->id)
            ->select('reviews.id','reviews.rating','reviews.comment','reviews.status','reviews.updated_at','products.name as product_name','users.id as user_id','users.name','users.avatar')
            ->distinct()
            ->paginate(1);
        
       return new ProductReviewCollection($reviews);
    }

    public function store_product(Request $request){

         
        if (addon_is_activated('seller_subscription')) {
            if (!seller_package_validity_check()) {
                
                $this->failed(translate('Please upgrade your package'));
            }
        }

        $slug = Str::slug($request->name);
        $same_slug_count = Product::where('slug', 'LIKE', $slug . '%')->count();
        $slug_suffix = $same_slug_count ? '-' . $same_slug_count + 1 : '';
        $slug .= $slug_suffix;

        $data = new Product;
        $data->slug = $slug;
        $data->added_by=$request->added_by;
        $data->user_id=$request->user_id;
        $data->category_id=$request->category_id;
        $data->name=$request->name;
        $data->description=$request->description;
        $data->unit_price=$request->unit_price;
        $data->photos=$request->photos;
        $data->thumbnail_img=$request->thumbnail_img;
        $data->brand_id=$request->brand_id;
        $data->colors=$request->colors;
        $data->choice_options=$request->choice_options;
        $data->attributes=$request->attribute_ids;
        $data->meta_title = $request->name;
        $data->meta_description = $request->description;
        $data->meta_img = $request->thumbnail_img;
        
        if($data->save()){
            
            $request->merge(['product_id' => $data->id]);
            $request->merge(['lang' => 'tr']);
            ProductTranslation::create($request->only([
                'lang', 'name', 'description', 'product_id'
            ]));

            Artisan::call('view:clear');
            Artisan::call('cache:clear');
    
            return $this->success(translate('Product has been created successfully'));
        }else{
            return $this->success(translate('Somethings went wrong.'));
        }

        

    }

	public function update_product(Request $request){
        try{
            if (addon_is_activated('seller_subscription')) {
                if (!seller_package_validity_check()) {
                    
                    $this->failed(translate('Please upgrade your package'));
                }
            }
     
            $slug = Str::slug($request->name);
            $same_slug_count = Product::where('slug', 'LIKE', $slug . '%')->count();
            $slug_suffix = $same_slug_count ? '-' . $same_slug_count + 1 : '';
            $slug .= $slug_suffix;    
            
            $data = Product::where('id',$request->id)->first();

            $string_photos=null;
            
            if($request->photos!=null && $request->photos!="" && $data->photos!=null && $data->photos!=""){
                $string_photos=$data->photos.",".$request->photos;
            }elseif($request->photos!=null && $request->photos!=""){
                $string_photos=$request->photos;
            }else{
                $string_photos=$data->photos;
            }
    
            $data->slug = $slug;
            $data->added_by=$request->added_by;
            $data->user_id=$request->user_id;
            $data->category_id=$request->category_id;
            $data->name=$request->name;
            $data->description=$request->description;
            $data->unit_price=$request->unit_price;
            $data->photos=$string_photos;
            $data->thumbnail_img=explode(',',$string_photos)[0];
            $data->brand_id=$request->brand_id;
            $data->colors=$request->colors;
            $data->choice_options=$request->choice_options;
            $data->attributes=$request->attribute_ids;
            $data->meta_title = $request->name;
            $data->meta_description = $request->description;
            $data->meta_img = $request->thumbnail_img;

            $request->merge(['product_id' => $request->id]);
            $request->merge(['lang' => 'tr']);
            ProductTranslation::where('lang', 'tr')
            ->where('product_id', $request->product_id)
            ->update($request->only([
            'lang', 'name', 'description', 'product_id'
            ]));
    
            if($data->save()){
                Artisan::call('view:clear');
                Artisan::call('cache:clear');
        
                return $this->success(translate('Product has been created successfully'));
            }else{
                return $this->failed(translate('Somethings went wrong.'));
            }
            
        }
        catch(\Exception $e){
            return $this->failed($e->getMessage());
        }      

    }

    
    public function productImageUpload($request)
    {

        $ids=array();
        
        foreach($request as $item ){
            $item = \json_decode($item);
            
            $filename = $item->filename;
            $realImage = \base64_decode($item->image);
            $array = \explode(".",$filename);
            $extension = $array[count($array)-1];
            $dir = public_path('uploads/all');

            $upload = new Upload;
            
            $newFileName = rand(10000000000, 9999999999) . date("YmdHis") . "." . $extension;
            $newFullPath = "$dir/$newFileName";

            $filehandler = fopen($newFullPath, 'wb' );
            fwrite($filehandler, realImage);
            fclose($filehandler); 
            //$file_put = file_put_contents($newFullPath, $realImage);

            $newPath = "uploads/all/$newFileName";

            if (env('FILESYSTEM_DRIVER') == 's3') {
                Storage::disk('s3')->put($newPath, file_get_contents(base_path('public/') .$newPath));
                unlink(base_path('public/') . $newPath);
            }

            $upload->extension = $extension;
            $upload->file_name = $newPath;
            $upload->user_id = auth()->user()->id;
            $upload->type = "image";
            $upload->save();
            array_push($ids, $upload->id);
        }
            return \implode(",",$ids);
       
    }

}
