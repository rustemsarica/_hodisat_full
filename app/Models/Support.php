<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    protected $with = ['support_translations'];

    public function getTranslation($field = '', $lang = false){
        $lang = $lang == false ? App::getLocale() : $lang;
        $support_translation = $this->support_translations->where('lang', $lang)->first();
        return $support_translation != null ? $support_translation->$field : $this->$field;
    }

    public function support_translations(){
      return $this->hasMany(SupportTranslation::class);
    }

}
