<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Last_login extends Model
{
    protected $table = "last_login";
    public $timestamps = false;
    public $primaryKey  = 'login_id';
    
    public function user(){
        return $this->hasOne('App\User','id','user_id_fk');
    }
}
