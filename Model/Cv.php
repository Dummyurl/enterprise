<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cv extends Model
{
    protected $table = "job_seeker_cv";
    public $timestamps = false;
    public $primaryKey  = 'js_cv_id';
}
