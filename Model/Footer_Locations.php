<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Footer_Locations extends Model
{
    protected $table = "footer_locations";
    public $timestamps = false;
    public $primaryKey  = 'location_id';
}
