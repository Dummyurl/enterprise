<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Microsite_details extends Model
{
    protected $table = "microsite_details";
    public $timestamps = false;
    public $primaryKey  = 'site_id';

    public function locations(){
    	return $this->hasMany('App\Model\Microsite_locations','detail_fk_id','site_id');
    }
    public function jobsposted(){
    	return $this->hasMany('App\Model\Job_post','user_id_fk','user_id_fk');
    }
    public function user_pack(){
    	return $this->hasOne('App\Model\User_package','user_package_id','user_package_id');
    }
}
