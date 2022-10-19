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
        return new SupportCollection($supports= Support::where('parent_id',0)->get());
    }
}
