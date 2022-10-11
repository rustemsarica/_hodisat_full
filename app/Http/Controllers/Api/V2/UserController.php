<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\UserCollection;
use App\Models\User;
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
            'shop_logo' => uploaded_asset($user->shop->logo),
            'cover_image' => uploaded_asset($user->shop->sliders),
            'description' => $user->shop->meta_description,
            'phone' => $user->phone,
            'vacation_mode' => (string)$user->vacation_mode
        ]);

    }

}
