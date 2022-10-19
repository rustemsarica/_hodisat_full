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
            return response()->json([
                'app_wishlist'=> $permissions->app_wishlist,
                'app_follow'=> $permissions->app_follow,
                'app_offers'=> $permissions->app_offers,
                'app_reviews'=> $permissions->app_reviews,
                'mail_wishlist'=> $permissions->mail_wishlist,
                'mail_follow'=> $permissions->mail_follow,
                'mail_offers'=> $permissions->mail_offers,
                'mail_reviews'=> $permissions->mail_reviews,
            ]);
        }else if($request->type=="get"){
            $permissions=UserNotificationPermission::firstOrCreate(['user_id'=>auth()->user()->id]);

            if($permissions->app_wishlist==null){
               $permissions=UserNotificationPermission::where(['user_id', auth()->user()->id])->first();
            }

            return response()->json([
                'app_wishlist'=> $permissions->app_wishlist,
                'app_follow'=> $permissions->app_follow,
                'app_offers'=> $permissions->app_offers,
                'app_reviews'=> $permissions->app_reviews,
                'mail_wishlist'=> $permissions->mail_wishlist,
                'mail_follow'=> $permissions->mail_follow,
                'mail_offers'=> $permissions->mail_offers,
                'mail_reviews'=> $permissions->mail_reviews,
            ]);
        }

    }

}
