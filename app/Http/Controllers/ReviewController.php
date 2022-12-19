<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Order;
use App\Models\Shop;
use Auth;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $reviews = Review::orderBy('created_at', 'desc')->paginate(15);
        return view('backend.product.reviews.index', compact('reviews'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $reviewable = false;

        if($order != null && $order->user_id == Auth::user()->id && $order->delivery_status == 'delivered' && Review::where('user_id', Auth::user()->id)->where('order_id', $order->id)->first() == null){
            $reviewable = true;
        }

        if(!$reviewable){
            flash(translate('Already evaluated'))->error();
        }

        $review = new Review;
        $review->order_id = $request->order_id;
        $review->user_id = Auth::user()->id;
        $review->seller_id = $request->seller_id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->viewed = '0';
        $review->save();


        flash(translate('Review has been submitted successfully'))->success();
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function updatePublished(Request $request)
    {
        $review = Review::findOrFail($request->id);
        $review->status = $request->status;
        $review->save();

        $shop = Shop::where('user_id', $review->seller_id)->first();
        $order = Order::findOrFail($review->order_id);


        if($request->status==1){

            $shop->num_of_reviews+=1;
            $shop->save();
        }else{
            $shop->num_of_reviews-=1;
            $shop->save();
        }

        if(Review::where('seller_id', $order->seller_id)->where('status', 1)->count() > 0){
            $shop->rating = Review::where('seller_id', $order->seller_id)->where('status', 1)->sum('rating')/Review::where('seller_id', $order->seller_id)->where('status', 1)->count();
        }
        else {
            $shop->rating = 0;
        }
        $shop->save();

        return 1;
    }

    public function product_review_modal(Request $request){
        $order = Order::where('id',$request->order_id)->first();
        $review = Review::where('user_id',Auth::user()->id)->where('order_id',$order->id)->first();
        return view('frontend.user.product_review_modal', compact('order','review'));
    }
}
