<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Landing_Locations extends Model
{
    protected $table = "landing_locations";
    public $timestamps = false;
    public $primaryKey  = 'landing_id';
}
