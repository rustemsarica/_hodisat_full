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
            'message' => translate('Successfully logged in'),
            'access_token' => "",
            'token_type' => 'Bearer',
            'expires_at' => null,
            'shop_id'=> 0,
            'logo' => "https://hodisat.com/public/uploads/avatar-place.png",
            'sliders' => null,
            'description' => "",
            'user' => [
                'id' => 0,
                'type' => "",
                'name' => "",
                'username'=> "",
                'email' => "",
                'phone' => "",
                'vacation_mode' => 0,
                'address_status' => false
            ]
        ];



        $token = PersonalAccessToken::findToken($request->access_token);
        if (!$token) {
            return response()->json($false_response);
        }

        $user = $token->tokenable;



        if ($user == null) {
            return response()->json($false_response);

        }
        $shop=Shop::where('user_id',$user->id)->first();
        return response()->json([
            'result' => true,
            'message' => translate('Successfully logged in'),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => null,
            'shop_id'=> $shop->id,
            'logo' => uploaded_asset($shop->logo),
            'sliders' => uploaded_asset($shop->sliders),
            'description' => $shop->meta_descriptions,
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'name' => $user->name,
                'username'=> $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'vacation_mode' => $user->vacation_mode,
                'address_status' => $user->address == null || $user->state == null || $user->city == null ? false : true
            ]
        ]);

    }

    public function userNotificationPermissions(Request $request)
    {
        if($request->type=="update"){
            UserNotificationPermission::where('user_id',auth()->user()->id)->update([
                'app_wishlist'=> $request->app_wishlist,
                'app_follow'=> $request->app_follow,
                'app_offers'=> $request->app_offers,
                'app_reviews'=> $request->app_reviews,
                'mail_wishlist'=> $request->mail_wishlist,
                'mail_follow'=> $request->mail_follow,
                'mail_offers'=> $request->mail_offers,
                'mail_reviews'=> $request->mail_reviews,
            ]);
            $permissions=UserNotificationPermission::where('user_id',auth()->user()->id)->first();
            return response()->json(['data'=>[
                ['key' => 'app_wishlist', 'name' => translate('Wishlist Notifications'), 'value' => $permissions->app_wishlist],
                ['key' => 'app_follow', 'name' => translate('Follow Notifications'), 'value' => $permissions->app_follow],
                ['key' => 'app_offers', 'name' => translate('Offer Notifications'), 'value' => $permissions->app_offers],
                ['key' => 'app_reviews', 'name' => translate('Review Notifications'), 'value' => $permissions->app_reviews],
                ['key' => 'mail_wishlist', 'name' => 'Mail '.translate('Wishlist Notifications'), 'value' => $permissions->mail_wishlist],
                ['key' => 'mail_follow', 'name' => 'Mail '.translate('Follow Notifications'), 'value' => $permissions->mail_follow],
                ['key' => 'mail_offers', 'name' => 'Mail '.translate('Offers Notifications'), 'value' => $permissions->mail_offers],
                ['key' => 'mail_reviews', 'name' => 'Mail '.translate('Review Notifications'), 'value' => $permissions->mail_reviews]
            ]]);
        }else if($request->type=="get"){
            $permissions=UserNotificationPermission::firstOrCreate(['user_id'=>auth()->user()->id]);

            if($permissions->app_wishlist==null){
               $permissions=UserNotificationPermission::where(['user_id', auth()->user()->id])->first();
            }

            return response()->json(['data'=>[
                ['key' => 'app_wishlist', 'name' => translate('Wishlist Notifications'), 'value' => $permissions->app_wishlist],
                ['key' => 'app_follow', 'name' => translate('Follow Notifications'), 'value' => $permissions->app_follow],
                ['key' => 'app_offers', 'name' => translate('Offer Notifications'), 'value' => $permissions->app_offers],
                ['key' => 'app_reviews', 'name' => translate('Review Notifications'), 'value' => $permissions->app_reviews],
                ['key' => 'mail_wishlist', 'name' => 'Mail '.translate('Wishlist Notifications'), 'value' => $permissions->mail_wishlist],
                ['key' => 'mail_follow', 'name' => 'Mail '.translate('Follow Notifications'), 'value' => $permissions->mail_follow],
                ['key' => 'mail_offers', 'name' => 'Mail '.translate('Offers Notifications'), 'value' => $permissions->mail_offers],
                ['key' => 'mail_reviews', 'name' => 'Mail '.translate('Review Notifications'), 'value' => $permissions->mail_reviews]
            ]]);
        }

    }

}
