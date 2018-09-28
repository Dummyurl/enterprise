<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    protected $table = "specialization";
    public $timestamps = false;
    public $primaryKey  = 'specialization_id';
}
