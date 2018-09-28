<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Application_re_reply extends Model
{
    protected $table = "application_re_reply";
    public $timestamps = true;
    public $primaryKey  = 'id';

   public function apply(){
    	return $this->hasone('App\Model\Applied_job','apply_id','apply_id_fk');
    }
}