<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    protected $table = "employer_details";
    public $timestamps = false;
    public $primaryKey  = 'employer_id';

    public function countries(){
        return $this->hasOne('App\Model\Countries','id','country');
    }
    public function states(){
    	return $this->hasOne('App\Model\States','id','state');
    }
    public function citys(){
    	return $this->hasOne('App\Model\Cities','id','city');
    }
    public function user(){
        return $this->hasOne('App\User','id','user_id_fk');
    }
    public function jobsposted(){
        return $this->hasMany('App\Model\Job_post','user_id_fk','user_id_fk');
    }
}
