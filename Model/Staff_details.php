<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Staff_details extends Model
{
    protected $table = "staff_details";
    public $timestamps = true;
    public $primaryKey  = 'id';

    public function Staff_group(){
    	return $this->hasOne('App\Model\Staff_group','id','group_id_fk');
    }
}
