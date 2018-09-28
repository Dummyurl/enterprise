<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Saver_package extends Model
{
    protected $table = "saver_package";
    public $timestamps = true;
    public $primaryKey  = 'saver_pack_id';

    public function addon_price()
    {
    	return $this->hasOne('App\Model\Addon_price','pack_id','saver_pack_id')->where('pack_type',1);
    }

   
}
