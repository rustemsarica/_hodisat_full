<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\ReviewCollection;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\User;

class ReviewController extends Controller
{
    public function index($id)
    {
        return new ReviewCollection(Review::where('seller_id', $id)->where('status', 1)->orderBy('updated_at', 'desc')->paginate(10));
    }

    public function submit(Request $request)
    {
        $order = Order::find($request->order_id);

        $reviewable = false;

        if($order != null && $order->user_id == auth()->user()->id && $order->delivery_status == 'confirmed' && Review::where('user_id', auth()->user()->id)->where('order_id', $order->id)->count() == 0){
            $reviewable = true;
        }

        if(!$reviewable){
            return response()->json([
                'result' => false,
                'message' => translate('You cannot review this product')
            ]);
        }

        $review = new Review;
        $review->order_id = $request->order_id;
        $review->user_id = auth()->user()->id;
        $review->seller_id = $request->seller_id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->viewed = 0;
        $review->save();


        return response()->json([
            'result' => true,
            'message' => translate('Review  Submitted')
        ]);
    }
}
