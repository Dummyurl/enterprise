<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Saved_job extends Model
{
    protected $table = "saved_job";
    public $timestamps = false;
    public $primaryKey  = 'saved_id';

     public function save_job(){
    	return $this->hasOne('App\Model\Job_post','job_id','job_id_fk');
    }
    public function user(){
    	return $this->hasOne('App\User','id','user_id_fk');
    }
}
