<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Job_preference extends Model
{
    protected $table = "job_seeker_job_preference";
    public $timestamps = false;
    public $primaryKey  = 'js_job_preference_id';
     public function countries(){
        return $this->hasOne('App\Model\Countries','id','country_id');
    }
    public function states(){
    	return $this->hasOne('App\Model\States','id','state_id');
    }
}
