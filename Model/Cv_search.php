<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cv_search extends Model
{
    protected $table = "cv_search";
    public $timestamps = false;
    public $primaryKey  = 'cv_search_id';

    public function details(){
        return $this->hasOne('App\Model\Cv_search_details','search_id_fk','cv_search_id');
    }

    public function user(){
    	return $this->hasOne('App\User','id','user_id_fk');
    }
    public function techskills(){
        return $this->hasMany('App\Model\Cv_search_techskills','search_id_fk','cv_search_id');
    }
}
