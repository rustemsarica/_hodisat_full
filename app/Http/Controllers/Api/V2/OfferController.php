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
        $productIds = Product::where('user_id', auth()->user()->id)->pluck('id')->toArray();

        if($request->status=="" || $request->status==null){
            $offers = Offer::whereIn('product_id',$productIds)->where('answer',null)->get();
            return new OfferCollection($offers);
        }

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

    public function create_offer(Request $request)
    {

        $product = Product::where('id',$request->product_id)->first();
        $min_offer_value = ( $product->unit_price / 100 ) * ( 100 - 20 );
        $unansweredOffers = Offer::where(['user_id'=> auth()->user()->id, 'product_id'=> $request->product_id, 'answer'=>null])->count();

        if($unansweredOffers>0){
            return response()->json([
                'status' => false,
                'message' => 'Teklifin değerlendirilmeden yeni bir teklif yapamazsın.'
            ]);
        }

        if($min_offer_value > $request->offer_value){
            return response()->json([
                'status' => false,
                'message' => 'Teklif değerin, minimum teklif değerinin altında.'
            ]);
        }

        if($product->unit_price  == $request->offer_value){
            return response()->json([
                'status' => false,
                'message' => 'Teklif değerin ürün fiyatının altında olmalı.'
            ]);
        }

        $offer = new Offer;
        $offer->product_id = $request->product_id;
        $offer->user_id = auth()->user()->id;
        $offer->offer_value = $request->offer_value;

        if($offer->save()){

            if (get_setting('google_firebase') == 1 && $product->user->device_token != null) {
                $request->device_token = $product->user->device_token;
                $request->title = "Teklif!";
                $request->text = $product->name." için ".$request->offer_value." ₺ değerinde teklif yapıldı";

                $request->type = "offer";
                $request->id = $product->id;
                $request->user_id = $product->user->id;
                $request->image = uploaded_asset($product->thumbnail_img);

                NotificationUtility::sendFirebaseNotification($request);
            }

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
        $offer = Offer::where('id', $request->id)->first();
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

    public function check_offer(Request $request)
    {
       $offer = Offer::where(['user_id'=>auth()->user()->id, 'product_id'=>$request->product_id])->get();
       return new OfferCollection($offer);
    }

}
