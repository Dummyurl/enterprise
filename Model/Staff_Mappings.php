<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Staff_Mappings extends Model
{
    protected $table = "staff_mappings";
    public $timestamps = true;
    public $primaryKey  = 'map_id';

    public function menu_data(){
        return $this->hasOne('App\Model\Staff_Menus','menu_id','menu_id');
    }
}
