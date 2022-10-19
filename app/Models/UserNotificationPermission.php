<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationPermission extends Model
{
    protected $fillable = [

        'user_id',
        'app_wishlist',
        'app_follow',
        'app_offers',
        'app_reviews',
        'mail_wishlist',
        'mail_follow',
        'mail_offers',
        'mail_reviews',
    ];

    public function user(){
    	return $this->belongsTo(User::class);
    }
}
