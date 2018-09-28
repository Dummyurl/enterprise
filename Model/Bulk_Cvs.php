<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Bulk_Cvs extends Model
{
    protected $table = "bulk_cvs";
    public $timestamps = true;
    public $primaryKey  = 'cv_id';
}
