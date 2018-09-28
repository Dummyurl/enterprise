<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Job_seeker_personal_details extends Model
{
    protected $table = "job_seeker_personal_details";
    public $timestamps = false;
    public $primaryKey  = 'js_personal_id';

    public function user(){
        return $this->hasOne('App\User','id','user_id_fk');
    }
}
