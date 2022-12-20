<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\OTPVerificationController;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\UserNotificationPermission;
use App\Notifications\AppEmailVerificationNotification;
use Hash;
use Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Artisan;


class AuthController extends Controller
{
    public function signup(Request $request)
    {
        if (User::where('email', $request->email_or_phone)->orWhere('phone', $request->email_or_phone)->first() != null) {
            return response()->json([
                'result' => false,
                'message' => translate('User already exists.'),
                'user_id' => 0
            ], 201);
        }

        if ($request->register_by == 'email') {
            $user = new User([
                'username'=> strtolower($request->username),
                'name' => $request->name,
                'email' => $request->email_or_phone,
                'password' => bcrypt($request->password),
                'verification_code' => rand(100000, 999999)
            ]);
        } else {
            $user = new User([
                'username'=> strtolower($request->username),
                'name' => $request->name,
                'phone' => $request->email_or_phone,
                'password' => bcrypt($request->password),
                'verification_code' => rand(100000, 999999),
                'user_type'=>'seller'
            ]);
        }

        $user->email_verified_at = null;
        if($user->email != null){
            if (BusinessSetting::where('type', 'email_verification')->first()->value != 1) {
                $user->email_verified_at = date('Y-m-d H:m:s');
            }
        }

        if ($user->email_verified_at == null) {
            if ($request->register_by == 'email') {
                try {
                    $user->notify(new AppEmailVerificationNotification());
                } catch (\Exception $e) {
                }
            } else {
                $otpController = new OTPVerificationController();
                $otpController->send_code($user);
            }
        }

        $user->save();

        $shop = new Shop;
        $shop->user_id = $user->id;
        $shop->save();

        $notif = new UserNotificationPermission;
        $notif->user_id = $user->id;
        $notif->save();

        //create token
        $user->createToken('tokens')->plainTextToken;

        return response()->json([
            'result' => true,
            'message' => translate('Registration Successful. Please verify and log in to your account.'),
            'user_id' => $user->id
        ], 201);
    }

    public function resendCode(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $user->verification_code = rand(100000, 999999);

        if ($request->verify_by == 'email') {
            $user->notify(new AppEmailVerificationNotification());
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }

        $user->save();

        return response()->json([
            'result' => true,
            'message' => translate('Verification code is sent again'),
        ], 200);
    }

    public function confirmCode(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();

        if ($user->verification_code == $request->verification_code) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->verification_code = null;
            $user->save();
            return response()->json([
                'result' => true,
                'message' => translate('Your account is now verified.Please login'),
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => translate('Code does not match, you can request for resending the code'),
            ], 200);
        }
    }

    public function login(Request $request)
    {
        /*$request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);*/

        $seller_condition = $request->has('user_type') && $request->user_type == 'seller';

        if ($seller_condition) {
            $user = User::whereIn('user_type', ['seller'])
                ->where('email', $request->email)
                ->orWhere('phone', $request->email)
                ->first();
        } else {
            $user = User::where('email', $request->email)
                ->orWhere('phone', $request->email)
                ->first();
        }


        if ($user != null) {
            if (Hash::check($request->password, $user->password)) {

                if ($user->email_verified_at == null) {
                    return response()->json(['result' => false, 'message' => translate('Please verify your account'), 'user' => null], 401);
                }
                return $this->loginSuccess($user);
            } else {
                return response()->json(['result' => false, 'message' => translate('Password is incorrect'), 'user' => null], 401);
            }
        } else {
            return response()->json(['result' => false, 'message' => translate('User not found'), 'user' => null], 401);
        }
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {

        $user = request()->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'result' => true,
            'message' => translate('Successfully logged out')
        ]);
    }

    public function socialLogin(Request $request)
    {
        if (!$request->provider) {
            return response()->json([
                'result' => false,
                'message' => translate('User not found'),
                'user' => null
            ]);
        }

        //
        switch ($request->social_provider) {
            case 'facebook':
                $social_user = Socialite::driver('facebook')->fields([
                    'name',
                    'first_name',
                    'last_name',
                    'email'
                ]);
                break;
            case 'google':
                $social_user = Socialite::driver('google')
                    ->scopes(['profile', 'email']);
                break;
            default:
                $social_user = null;
        }
        if ($social_user == null) {
            return response()->json(['result' => false, 'message' => translate('No social provider matches'), 'user' => null]);
        }

        $social_user_details = $social_user->userFromToken($request->access_token);

        if ($social_user_details == null) {
            return response()->json(['result' => false, 'message' => translate('No social account matches'), 'user' => null]);
        }

        //

        $existingUserByProviderId = User::where('provider_id', $request->provider)->first();

        if ($existingUserByProviderId) {
            return $this->loginSuccess($existingUserByProviderId);
        } else {

            $existingUserByMail = User::where('email', $request->email)->first();
            if($existingUserByMail){

                return response()->json(['result' => false, 'message' => translate('You can not login with this provider'), 'user' => null]);
            }else{

            $user = new User([
                'username'=> Str::slug(explode(' ',$request->name)[0]),
                'name' => $request->name,
                'email' => $request->email,
                'provider_id' => $request->provider,
                'email_verified_at' => Carbon::now()
            ]);
            $user->save();
            $user->username = $user->username.$user->id;
            $user->save();

            $shop = new Shop;
            $shop->user_id = $user->id;
            $shop->save();

            $notif = new UserNotificationPermission;
            $notif->user_id = $user->id;
            $notif->save();
        }
        }
        return $this->loginSuccess($user);
    }

    protected function loginSuccess($user)
    {
        $token = $user->createToken('API Token')->plainTextToken;
        $shop=Shop::where('user_id',$user->id)->first();
        return response()->json([
            'result' => true,
            'message' => translate('Successfully logged in'),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => null,
            'shop_id'=> $shop->id,
            'logo' => $shop->logo == null ? "https://hodisat.com/public/uploads/avatar-place.png" : uploaded_asset($shop->logo),
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

    public function checkUsername(Request $request)
    {
        $user = User::where('username', $request->username)->count();
        if($user>0){
            return response()->json([
                'result' => false
            ]);
        }

        return response()->json([
            'result' => true
        ]);
    }

    public function delete(Request $request)
    {

        $user = request()->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        DB::table('wishlists')->where("user_id", $user->id)->delete();
        DB::table('addresses')->where("user_id", $user->id)->delete();
        $shop=DB::table('shops')->where("user_id", $user->id)->first();

        $products = Product::where('user_id', $shop->user_id)->pluck('id')->toArray();
        if(count($products)>0){
            DB::table('carts')->whereIn('product_id', $products)->delete();
            DB::table('wishlists')->whereIn('product_id', $products)->delete();
            DB::table('offers')->whereIn("product_id", $products)->delete();
            DB::table('firebase_notifications')->where("item_type", 'product')->whereIn('item_type_id', $products)->delete();

            DB::table('products')->where("user_id", $user->id)->delete();
        }

        DB::table('carts')->where("user_id", $user->id)->delete();
        DB::table('offers')->where("user_id", $user->id)->delete();

        DB::table('firebase_notifications')->where("receiver_id", $user->id)->delete();
        DB::table('firebase_notifications')->where("item_type", 'user')->where('item_type_id', $shop->id)->delete();
        DB::table('notifications')->where("notifiable_id", $user->id)->delete();

        DB::table('blocked_users')->where("user_id", $user->id)->orWhere('blocked_user', $user->id)->delete();
        DB::table('follows')->where("user_id", $user->id)->orWhere('followed_user_id', $user->id)->delete();

        $conversations=DB::table('conversations')->where("sender_id", $user->id)->orWhere("receiver_id", $user->id);
        DB::table('messages')->whereIn("conversation_id", $conversations->pluck('id')->toArray())->delete();
        $conversations->delete();

        DB::table('reviews')->where("user_id", $user->id)->delete();
        DB::table('reviews')->where("seller_id", $user->id)->delete();

        DB::table('user_notification_permissions')->where("user_id", $user->id)->delete();
        DB::table('wallets')->where("user_id", $user->id)->delete();

		User::where("id", $user->id)->delete();
        $shop->delete();

        Artisan::call('cache:clear');

        return response()->json([
            'result' => true,
            'message' => translate('Account deleted successfully')
        ]);
    }
}
