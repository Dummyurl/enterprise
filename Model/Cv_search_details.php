<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cv_search_details extends Model
{
    protected $table = "cv_search_details";
    public $timestamps = false;
    public $primaryKey  = 'id';
    public function functional_area(){
        return $this->hasMany('App\Model\Cv_search_techskills','search_id_fk','cv_search_id');
    }
}
