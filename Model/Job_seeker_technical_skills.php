<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Job_seeker_technical_skills extends Model
{
    protected $table = "job_seeker_technical_skills";
    public $timestamps = false;
    public $primaryKey  = 'js_skills_id';
}
