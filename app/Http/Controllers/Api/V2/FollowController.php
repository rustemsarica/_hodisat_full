<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\FollowCollection;
use App\Http\Resources\V2\NotificationsCollection;

use App\Models\Follow;
use App\Models\Shop;
use App\Models\FirebaseNotification;
use App\Models\user;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Utility\NotificationUtility;

class FollowController extends Controller
{
    public function checkFollow(Request $request)
    {
       $follow=Follow::where(['user_id' => auth()->user()->id, 'followed_user_id' => $request->followed_user_id])->count();
       if($follow>0){
        return response()->json(['result' => true]);
       }else{
        return response()->json(['result' => false]);
       }
    }

    public function add(Request $request)
    {
        $product = Follow::where(['followed_user_id' => $request->followed_user_id, 'user_id' => auth()->user()->id])->count();
        if ($product == 0) {

            Follow::create(
                ['user_id' =>auth()->user()->id, 'followed_user_id' => $request->followed_user_id]
            );

            $user = User::where('id',$request->followed_user_id)->first();
            if (get_setting('google_firebase') == 1 && $user->device_token != null) {
                $request->device_token = $user->device_token;
                $request->title = "Yeni takipçi!";
                $request->text = $user->username.", seni takip etmeye başladı.";

                $request->type = "user";
                $request->id = $user->shop->id;
                $request->user_id = $user->id;
                $request->image = uploaded_asset($user->shop->logo);

                NotificationUtility::sendFirebaseNotification($request);
            }

            return response()->json(['result' => true], 200);
        }

    }

    public function remove(Request $request)
    {
        $product = Follow::where(['followed_user_id' => $request->followed_user_id, 'user_id' =>  auth()->user()->id])->count();
        if ($product > 0) {

            Follow::where(['followed_user_id' => $request->followed_user_id, 'user_id' => auth()->user()->id])->delete();

            return response()->json(['result' => true], 200);
        }
    }

    public function getFollowers(Request $request)
    {
        $user_ids = Follow::where('followed_user_id', $request->user_id)->pluck("user_id")->toArray();
        $shops = Shop::whereIn('user_id', $user_ids);

        return new FollowCollection($shops->get());
    }

    public function getFollowing(Request $request)
    {
        $user_ids = Follow::where('user_id', $request->user_id)->pluck("followed_user_id")->toArray();
        $shops = Shop::whereIn('user_id', $user_ids);

        return new FollowCollection($shops->get());
    }

	public function user_notifications()
    {

         $notifications =  FirebaseNotification::where('receiver_id', auth()->user()->id)->latest('created_at')->paginate(15);
         return new NotificationsCollection($notifications);

    }

    public function block_user(Request $request)
    {
        $user=DB::table('blocked_users')->where(['user_id'=> auth()->user()->id, 'blocked_user' => $request->blocked_user])->count();

        if($user>0){
            DB::table('blocked_users')->where(['user_id'=> auth()->user()->id, 'blocked_user' => $request->blocked_user])->delete();
            return response()->json(['result' => true, 'message'=> 'Kullanıcının engeli kaldırıldı.']);
        }else{
            DB::table('blocked_users')->insert([
                'user_id'=> auth()->user()->id,
                'blocked_user' => $request->blocked_user
            ]);
            return response()->json(['result' => true, 'message'=> 'Kullanıcı engellendi.']);
        }
    }

	public function blockedUsers(Type $var = null)
    {
        $users=DB::table('blocked_users')->where('user_id', auth()->user()->id)->pluck('blocked_user')->toArray();
        $shops=Shop::whereIn('user_id',$users)->get();
        return new FollowCollection($shops);
    }

}
