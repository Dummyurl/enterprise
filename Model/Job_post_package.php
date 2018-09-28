<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Job_post_package extends Model
{
    protected $table = "job_post_package";
    public $timestamps = false;
    public $primaryKey  = 'job_post_pack_id';
    
    public function pack(){
    	return $this->hasOne('App\Model\Package','package_id','package_id_fk');
    }
    public function add_on(){
    	return $this->hasMany('App\Model\Addon_package','package_id_fk','package_id_fk');
    }

   	public function addon_price()
    {
    	return $this->hasOne('App\Model\Addon_price','pack_id','job_post_pack_id')->where('pack_type',2);
    }
}
