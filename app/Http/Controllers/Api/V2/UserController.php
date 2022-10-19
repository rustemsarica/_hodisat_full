<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\UserCollection;
use App\Models\User;
use App\Models\UserNotificationPermission;
use Illuminate\Http\Request;

use Laravel\Sanctum\PersonalAccessToken;


class UserController extends Controller
{
    public function info($id)
    {
        return new UserCollection(User::where('id', auth()->user()->id)->get());
    }

    public function updateName(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->update([
            'name' => $request->name
        ]);
        return response()->json([
            'message' => translate('Profile information has been updated successfully')
        ]);
    }

    public function getUserInfoByAccessToken(Request $request)
    {

        $false_response = [
            'result' => false,
            'id' => 0,
            'name' => "",
            'email' => "",
            'username' => "",
            'shop_logo' => "https://hodisat.com/public/uploads/avatar-place.png",
            'cover_image' => "https://hodisat.com/public/uploads/bg.jpg",
            'description' => "",
            'phone' => "",
            'vacation_mode'=> ""
        ];



        $token = PersonalAccessToken::findToken($request->access_token);
        if (!$token) {
            return response()->json($false_response);
        }

        $user = $token->tokenable;



        if ($user == null) {
            return response()->json($false_response);

        }

        return response()->json([
            'result' => true,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'shop_logo' => $user->shop->logo==null ? "https://hodisat.com/public/uploads/avatar-place.png" : uploaded_asset($user->shop->logo),
            'cover_image' => uploaded_asset($user->shop->sliders),
            'description' => $user->shop->meta_description,
            'phone' => $user->phone,
            'vacation_mode' => (string)$user->vacation_mode
        ]);

    }

    public function userNotificationPermissions(Request $request)
    {
        if($request->type=="update"){
            $permissions=UserNotificationPermission::where('user_id',auth()->user()->id)->update([
                'app_wishlist'=> $request->app_wishlist,
                'app_follow'=> $request->app_follow,
                'app_offers'=> $request->app_offers,
                'app_reviews'=> $request->app_reviews,
                'mail_wishlist'=> $request->mail_wishlist,
                'mail_follow'=> $request->mail_follow,
                'mail_offers'=> $request->mail_offers,
                'mail_reviews'=> $request->mail_reviews,
            ]);
            return response()->json(['data'=>[
                'item' => [ 'key' => 'app_wishlist', 'name' => traslate('Wishlist Notifications'), 'value' => $permissions->app_wishlist],
                'item' => [ 'key' => 'app_follow', 'name' => translate('Follow Notifications'), 'value' => $permissions->app_follow],
                'item' => [ 'key' => 'app_offers', 'name' => translate('Offer Notifications'), 'value' => $permissions->app_offers],
                'item' => [ 'key' => 'app_reviews', 'name' => translate('Review Notifications'), 'value' => $permissions->app_reviews],
                'item' => [ 'key' => 'mail_wishlist', 'name' => 'Mail '.translate('Wishlist Notifications'), 'value' => $permissions->mail_wishlist],
                'item' => [ 'key' => 'mail_follow', 'name' => 'Mail '.translate('Follow Notifications'), 'value' => $permissions->mail_follow],
                'item' => [ 'key' => 'mail_offers', 'name' => 'Mail '.translate('Offers Notifications'), 'value' => $permissions->mail_offers],
                'item' => [ 'key' => 'mail_reviews', 'name' => 'Mail '.translate('Review Notifications'), 'value' => $permissions->mail_reviews]
            ]]);
        }else if($request->type=="get"){
            $permissions=UserNotificationPermission::firstOrCreate(['user_id'=>auth()->user()->id]);

            if($permissions->app_wishlist==null){
               $permissions=UserNotificationPermission::where(['user_id', auth()->user()->id])->first();
            }

            return response()->json(['data'=>[
                'item' => [ 'key' => 'app_wishlist', 'name' => traslate('Wishlist Notifications'), 'value' => $permissions->app_wishlist],
                'item' => [ 'key' => 'app_follow', 'name' => translate('Follow Notifications'), 'value' => $permissions->app_follow],
                'item' => [ 'key' => 'app_offers', 'name' => translate('Offer Notifications'), 'value' => $permissions->app_offers],
                'item' => [ 'key' => 'app_reviews', 'name' => translate('Review Notifications'), 'value' => $permissions->app_reviews],
                'item' => [ 'key' => 'mail_wishlist', 'name' => 'Mail '.translate('Wishlist Notifications'), 'value' => $permissions->mail_wishlist],
                'item' => [ 'key' => 'mail_follow', 'name' => 'Mail '.translate('Follow Notifications'), 'value' => $permissions->mail_follow],
                'item' => [ 'key' => 'mail_offers', 'name' => 'Mail '.translate('Offers Notifications'), 'value' => $permissions->mail_offers],
                'item' => [ 'key' => 'mail_reviews', 'name' => 'Mail '.translate('Review Notifications'), 'value' => $permissions->mail_reviews]
            ]]);
        }

    }

}
