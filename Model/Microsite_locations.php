<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Microsite_locations extends Model
{
    protected $table = "microsite_locations";
    public $timestamps = false;
    public $primaryKey  = 'loc_id';
}
