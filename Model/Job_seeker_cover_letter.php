<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Job_seeker_cover_letter extends Model
{
    protected $table = "job_seeker_cover_letter";
    public $timestamps = false;
    public $primaryKey  = 'js_cover_id';
}
