<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Seminar_details extends Model
{
    protected $table = "job_seeker_seminar_details";
    public $timestamps = false;
    public $primaryKey  = 'js_seminar_id';
}
