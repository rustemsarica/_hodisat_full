<?php

namespace App\Http\Controllers\Seller;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use Auth;
use App\Models\ProductsImport;
use PDF;
use Excel;

class ProductBulkUploadController extends Controller
{
    public function index()
    {
        
        return back();
        
    }

    public function pdf_download_category()
    {
        $categories = Category::all();

        return PDF::loadView('backend.downloads.category',[
            'categories' => $categories,
        ], [], [])->download('category.pdf');
    }

    public function pdf_download_brand()
    {
        $brands = Brand::all();

        return PDF::loadView('backend.downloads.brand',[
            'brands' => $brands,
        ], [], [])->download('brands.pdf');
    }

    public function bulk_upload(Request $request)
    {
        if($request->hasFile('bulk_file')){
            $import = new ProductsImport;
            Excel::import($import, request()->file('bulk_file'));
        }
        
        return back();
    }

}
