<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class User_package extends Model
{
    protected $table = "user_package";
    public $timestamps = false;
    public $primaryKey  = 'user_package_id';

    public function pack(){
    	return $this->hasMany('App\Model\Package','package_id','package_id_fk');
    }
    public function packa(){
        return $this->hasOne('App\Model\Package','package_id','package_id_fk');
    }
    public function user(){
    	return $this->hasOne('App\User','id','user_id_fk');
    }
    public function user_detail(){
    	return $this->hasOne('App\Model\Employer','user_id_fk','user_id_fk');
    }

   
}
