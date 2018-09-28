<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PGSpecialization extends Model
{
    protected $table = "pgspecialization";
    public $timestamps = false;
    public $primaryKey  = 'pgs_id';
}
