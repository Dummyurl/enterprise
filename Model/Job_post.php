<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Job_post extends Model
{
    protected $table = "job_post";
    public $timestamps = false;
    public $primaryKey  = 'job_id';

    public function key_skill(){
        return $this->hasMany('App\Model\Job_post_keyskills','job_id_fk','job_id');
    }
    public function abuses(){
        return $this->hasMany('App\Model\Report_Abuse','job_id','job_id');
    }
    public function save_job(){
        return $this->hasOne('App\Model\Saved_job','job_id_fk','job_id')
                    ->where('user_id_fk', Auth::user()->id);
    }
    public function applied_job(){
        return $this->hasOne('App\Model\Applied_job','job_id_fk','job_id')
                    ->where('user_id_fk', Auth::user()->id);
    }
    public function user(){
        return $this->hasOne('App\User','id','user_id_fk');
    }
    public function employer_details(){
        return $this->hasOne('App\Model\Employer','user_id_fk','user_id_fk');
    }
    public function user_package(){
        return $this->hasOne('App\Model\User_package','user_package_id','user_package_id');
    }
}
