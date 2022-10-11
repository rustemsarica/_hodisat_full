<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\OfferCollection;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;

class OfferController extends Controller
{

    public function offers(Request $request)
    {
        if($request->status==""){
            $request->status=null;
        }
        $productIds = Product::where('user_id', auth()->user()->id)->pluck('id')->toArray();
        $offers = Offer::whereIn('product_id',$productIds)->where('answer',$request->status)->get();
        return new OfferCollection($offers);
    }

    public function myOffers(Request $request)
    {
        if($request->status==""){
            $request->status=null;
        }
        $offers = Offer::where('user_id',auth()->user()->id)->where('answer',$request->status)->get();
        return new OfferCollection($offers);
    }

    public function createOffer(Request $request)
    {
        $product = Product::where('id',$request->product_id)->first();


        $offer = new Offer;
        $offer->product_id = $request->product_id;
        $offer->user_id = auth()->user()->id;
        $offer->offer_value = $request->offer_value;

        if($offer->save()){

                return response()->json([
                    'status' => true,
                    'message' => 'Teklifin satıcıya iletildi.'
                ]);

        }else{
            return response()->json([
                'status' => false,
                'message' => 'Bir sorun oluştu.'
            ]);
        }

    }

    public function answer(Request $request)
    {
        $offer = Offer->where(['product_id'=> $request->product_id, 'user_id' => $request->user_id, 'answer'=> null])->first();
        $offer->answer = $request->answer;
        if($offer->save()){
            return response()->json([
                'status' => true,
                'message' => 'Teklif cevaplandı.'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu'
            ]);
        }
    }


}
