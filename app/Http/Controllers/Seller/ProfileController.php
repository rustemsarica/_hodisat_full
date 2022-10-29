<?php

namespace App\Http\Controllers\Seller;

use App\Http\Requests\SellerProfileRequest;
use App\Models\User;
use Auth;
use Hash;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $addresses = $user->addresses;
        return view('seller.profile.index', compact('user','addresses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SellerProfileRequest $request , $id)
    {
        if($request->has('payment_setting')){
            $seller = Seller::where('user_id', $id)->first();
            $seller->bank_name= $request->bank_name;
            $seller->bank_acc_name= $request->bank_acc_name;
            if($seller->save()){
                flash(translate('Your Profile has been updated successfully!'))->success();
                return back();
            }
            flash(translate('Sorry! Something went wrong.'))->error();
            return back();
        }
        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->phone = $request->phone;

        if($request->new_password != null && ($request->new_password == $request->confirm_password)){
            $user->password = Hash::make($request->new_password);
        }


        $user->save();

        flash(translate('Your Profile has been updated successfully!'))->success();
        return back();
    }
}
