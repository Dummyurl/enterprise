<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $table = "folder";
    public $timestamps = false;
    public $primaryKey  = 'folder_id';

    public function folder_share(){
        return $this->hasMany('App\Model\Folder_share','folder_id_fk','folder_id');
    }
    public function user(){
        return $this->hasOne('App\User','id','user_id_fk');
    }
}
