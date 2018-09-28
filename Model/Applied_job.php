<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Applied_job extends Model
{
    protected $table = "applied_job";
    public $timestamps = false;
    public $primaryKey  = 'apply_id';

    public function job(){
    	return $this->hasOne('App\Model\Job_post','job_id','job_id_fk');
    }
     public function applied_user(){
    	return $this->hasMany('App\User','id','user_id_fk');
    }
     public function applied_user2(){
        return $this->hasOne('App\User','id','user_id_fk');
    }
    public function reply(){
    	return $this->hasMany('App\Model\Application_reply','apply_id_fk','apply_id');
    }
}
