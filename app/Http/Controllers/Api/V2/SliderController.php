<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\SliderCollection;

class SliderController extends Controller
{
    public function sliders()
    {

            return new SliderCollection(json_decode(get_setting('home_slider_images'), true));

    }

    public function bannerOne()
    {

            return new SliderCollection(json_decode(get_setting('home_banner1_images')==null ? '[]' : get_setting('home_banner1_images'), true));

    }

    public function bannerTwo()
    {

            return new SliderCollection(json_decode(get_setting('home_banner2_images')==null ? '[]' : get_setting('home_banner2_images'), true));

    }

    public function bannerThree()
    {

        return new SliderCollection(json_decode(get_setting('home_banner3_images')==null ? '[]' : get_setting('home_banner3_images'), true));

    }
}
