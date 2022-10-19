<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\GeneralSettingCollection;
use App\Http\Resources\V2\SupportCollection;
use App\Models\GeneralSetting;
use App\Models\Support;

use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    public function index()
    {
        return new GeneralSettingCollection(GeneralSetting::all());
    }

    public function supports(Request $request)
    {
        if($request->parent_id>0){
            return new SupportCollection(Support::where('id',$request->parent_id)->orWhere('parent_id',$request->parent_id)->get());
        }else{
           return new SupportCollection(Support::where('parent_id',0)->get());
        }

    }
}
