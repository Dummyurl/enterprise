<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Folder_share extends Model
{
    protected $table = "folder_share";
    public $timestamps = true;
    public $primaryKey  = 'share_id';
    public function user(){
        return $this->hasOne('App\User','id','employer_id_fk');
    }
}
