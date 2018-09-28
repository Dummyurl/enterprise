<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Application_reply extends Model
{
    protected $table = "application_reply";
    public $timestamps = false;
    public $primaryKey  = 'reply_id';

   public function apply(){
    	return $this->hasone('App\Model\Applied_job','apply_id','apply_id_fk');
    }
}
