<?php

namespace App\Http\Controllers\Api\V2\Seller;

use App\Http\Resources\V2\Seller\ProductResource;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\FirebaseNotification;
use Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Upload;
use Storage;

use Mail;
use App\Mail\ProductMailManager;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc')->paginate(10);
        return new ProductMiniCollection($products);
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

        if (Product::destroy($id)) {
            Cart::where('product_id', $id)->delete();
            FirebaseNotification::where('item_type_id', $id)->where('item_type', 'product')->delete();
            FirebaseNotification::where('item_type_id', $id)->where('item_type', 'offer')->delete();

            return $this->success(translate('Product has been deleted successfully'));

            Artisan::call('view:clear');
            Artisan::call('cache:clear');
        }
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
        $data->added_by=auth()->user()->user_type;
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


        if (auth()->user()->user_type == 'seller') {
            if (get_setting('product_approve_by_admin') == 1) {
                $data->approved = 0;
            }
        }

        if($data->save()){

                $admins = User::where('user_type', 'admin')->get();

                foreach( $admins as $admin){
                    $array['view'] = 'emails.product';
                    $array['subject'] = 'Yeni Ürün';
                    $array['from'] = env('MAIL_FROM_ADDRESS');
                    $array['content'] = 'Yeni ürün yüklendi.';
                    $array['sender'] = $data->user->name;
                    $array['product'] = $data->name;
                    $array['date'] = $data->created_at;
                    try {
                        if($admin->email!=null && $admin->email!=""){
                            Mail::to($admin->email)->queue(new ProductMailManager($array));
                        }
                    } catch (\Exception $e) {
                        // dd($e->getMessage());
                    }
                }

            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return $this->success(translate('Product has been created successfully'));
        }else{
            return $this->failed(translate('Somethings went wrong.'));
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
