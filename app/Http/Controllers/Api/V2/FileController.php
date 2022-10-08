<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\UploadedFileCollection;
use App\Models\Upload;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Storage;
class FileController extends Controller
{
    public function index(){
        $all_uploads = (auth()->user()->user_type == 'seller') ? Upload::where('user_id',auth()->user()->id) : Upload::query();
        

        $all_uploads = $all_uploads->paginate(20)->appends(request()->query());

       return new UploadedFileCollection($all_uploads);

    }

    // any  base 64 image through uploader
    public function imageUpload(Request $request)
    {

        $type = array(
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
        );

        try {
            
            
            $image = $request->image;
            $filename = $request->filename;
            $realImage = base64_decode($image);
            $array= \explode(".",$filename);
            $extension = $array[count($array)-1];
            $dir = public_path('uploads/all');

            $upload = new Upload;

            if (!isset($type[$extension])) {
                return response()->json([
                    'result' => false,
                    'message' => translate("Only image can be uploaded"),
                    'path' => "err",
                    'upload_id' => 0
                ]);
            }

            
            $newFileName = rand(10000000000, 9999999999) . date("YmdHis") . "." . $extension;
            $newFullPath = "$dir/$newFileName";

            $file_put = file_put_contents($newFullPath, $realImage);

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => translate("Uploading error"),
                    'path' => "",
                    'upload_id' => 0
                ]);
            }

            $newPath = "uploads/all/$newFileName";

            if (env('FILESYSTEM_DRIVER') == 's3') {
                Storage::disk('s3')->put($newPath, file_get_contents(base_path('public/') . $newPath));
                unlink(base_path('public/') . $newPath);
            }

            $upload->extension = $extension;
            $upload->file_name = $newPath;
            $upload->user_id = auth()->user()->id;
            $upload->type = $type[$upload->extension];
            $upload->save();
            return response()->json([
                'result' => true,
                'message' => translate("Image updated"),
                'path' => uploaded_asset($upload->id),
                'upload_id' => $upload->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
                'path' => "",
                'upload_id' => 0
            ]);
        }
    }

    public function productImageUpload(Request $request)
    {

        $type = array(
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
        );

        $ids=array();
        try{
            foreach($request->data as $item ){
                $item = \json_decode($item);
            
                $filename=$item->filename;
                $realImage = base64_decode($item->image);
                $array= \explode(".",$filename);
                $extension =$array[count($array)-1];
                $dir = public_path('uploads/all');

                if (!isset($type[$extension])) {
                    return response()->json([
                        'result' => false,
                        'message' => translate("Only image can be uploaded"),
                        'path' => "",
                        'upload_id' => 0
                    ]);
                }


                $upload = new Upload;
                
                $newFileName = rand(10000000000, 9999999999) . date("YmdHis") . "." . $extension;
                $newFullPath = "$dir/$newFileName";

                $file_put = file_put_contents($newFullPath, $realImage);

                if ($file_put == false) {
                    return response()->json([
                        'result' => false,
                        'message' => translate("Uploading error"),
                        'path' => "",
                        'upload_id' => 0
                    ]);
                }

                $newPath = "uploads/all/$newFileName";

                if (env('FILESYSTEM_DRIVER') == 's3') {
                    Storage::disk('s3')->put($newPath, file_get_contents(base_path('public/') . $newPath));
                    unlink(base_path('public/') . $newPath);
                }

                $upload->extension = $extension;
                $upload->file_name = $newPath;
                $upload->user_id = auth()->user()->id;
                $upload->type = $type[$upload->extension];
                $upload->save();
                array_push($ids, $upload->id);
            }
            return response()->json([
                'result' => true,
                'message' => translate("Image updated"),
                'upload_ids' => \implode(",",$ids)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
                'upload_ids' => ""
            ]);
        }
    }

    public function imageDelete($productId,$imageId)
    {
        try {
            $product=Product::where('id',$productId)->first();
            $photos=explode(',',$product->photos);
            $key = $photos[$imageId];
            unset($photos[$imageId]);

            if(!empty($photos)){
                $photos = array_values($photos);
                $product->thumbnail_img=$photos[0];
                $product->photos = \implode(",",$photos); 
            }else{
                $product->photos = null;
                $product->thumbnail_img=null; 
            }
            
            $product->save();
            $upload=Upload::where('id',$key)->first();
            File::delete($upload->file_name);
            
            Upload::where('id',$key)->delete();
            
            return response()->json([
                'result' => true,
                'message' => "Image deleted",
            ]);
        } catch (\Exception $e) {
                return response()->json([
                    'result' => false,
                    'message' => $e->getMessage(),
                ]);
            }
        }
	public function delete($imageId)
    {
        $upload=Upload::where('id',$imageId)->first();
        File::delete($upload->file_name);            
        Upload::where('id',$imageId)->delete();
        return response()->json([
            'result' => true,
            'message' => "Image deleted",
        ]);
    }
}
