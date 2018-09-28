<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cvsearch_save extends Model
{
    protected $table = "cvsearch_save";
    public $timestamps = false;
    public $primaryKey  = 'id';

    public function searches(){
        return $this->hasOne('App\Model\Cv_search','cv_search_id','search_id_fk');
    }
}
