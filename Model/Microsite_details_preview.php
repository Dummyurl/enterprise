<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Microsite_details_preview extends Model
{
    protected $table = "microsite_details_preview";
    public $timestamps = false;
    public $primaryKey  = 'site_id';

    public function locations(){
    	return $this->hasMany('App\Model\Microsite_locations','detail_fk_id','site_id');
    }
    public function jobsposted(){
    	return $this->hasMany('App\Model\Job_post','user_id_fk','user_id_fk');
    }
}
