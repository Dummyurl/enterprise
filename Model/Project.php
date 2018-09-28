<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = "job_seeker_projects";
    public $timestamps = false;
    public $primaryKey  = 'js_project_id';
}
