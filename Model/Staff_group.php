<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Staff_group extends Model
{
    protected $table = "staff_group";
    public $timestamps = true;
    public $primaryKey  = 'id';

    public function staff_permition(){
    	return $this->hasMany('App\Model\Staff_Mappings','group_id_fk','id');
    }
}
