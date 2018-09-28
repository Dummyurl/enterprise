<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Jobsby_Locations extends Model
{
    protected $table = "jobsby_locations";
    public $timestamps = true;
    public $primaryKey  = 'location_id';
}
