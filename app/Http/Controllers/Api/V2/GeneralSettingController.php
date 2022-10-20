<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\GeneralSettingCollection;
use App\Models\GeneralSetting;

use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    public function index()
    {
        return new GeneralSettingCollection(GeneralSetting::all());
    }


}
