<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Staff_Menus extends Model
{
    protected $table = "staff_menus";
    public $timestamps = true;
    public $primaryKey  = 'menu_id';
}
