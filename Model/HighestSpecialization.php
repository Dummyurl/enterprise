<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class HighestSpecialization extends Model
{
    protected $table = "highest_specialization";
    public $timestamps = true;
    public $primaryKey  = 'hs_id';
}
