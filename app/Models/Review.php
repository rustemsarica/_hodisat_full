<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
  public function user(){
    return $this->belongsTo(User::class);
  }

  public function order(){
    return $this->belongsTo(Order::class);
  }

  public function seller(){
    return $this->belongsTo(User::class, 'seller_id', 'id');
  }
}
