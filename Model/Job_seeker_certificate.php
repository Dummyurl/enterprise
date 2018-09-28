<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Job_seeker_certificate extends Model
{
    protected $table = "job_seeker_certificate";
    public $timestamps = false;
    public $primaryKey  = 'js_certificate_id';
}
