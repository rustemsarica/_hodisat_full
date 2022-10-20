<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTranslation extends Model
{
  protected $fillable = ['title','text', 'lang', 'support_id'];

  public function support(){
    return $this->belongsTo(Support::class);
  }
}
