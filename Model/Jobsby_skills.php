<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Jobsby_skills extends Model
{
    protected $table = "jobsby_skills";
    public $timestamps = true;
    public $primaryKey  = 'skill_id';
}
