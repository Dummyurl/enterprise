<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Branding_package extends Model
{
    protected $table = "branding_package";
    public $timestamps = false;
    public $primaryKey  = 'branding_pack_id';

    public function pack(){
    	return $this->hasOne('App\Model\Package','package_id','package_id_fk');
    }
    public function addon_price()
    {
    	return $this->hasOne('App\Model\Addon_price','pack_id','branding_pack_id')->where('pack_type',3);
    }
}
