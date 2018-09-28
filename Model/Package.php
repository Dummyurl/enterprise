<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $table = "package";
    public $timestamps = false;
    public $primaryKey  = 'package_id';

    public function saver_pack(){
    	return $this->hasOne('App\Model\Saver_package','package_id_fk','package_id');
    }
    public function cv_pack(){
    	return $this->hasOne('App\Model\Cv_package','package_id_fk','package_id');
    }
    public function job_posting_pack(){
    	return $this->hasMany('App\Model\Job_post_package','package_id_fk','package_id');
    }
    public function branding_pack(){
    	return $this->hasOne('App\Model\Branding_package','package_id_fk','package_id');
    }
    public function job_post_pack(){
        return $this->hasOne('App\Model\Job_post_package','package_id_fk','package_id');
    }
    
    
}
