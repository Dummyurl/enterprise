<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Job_post_keyskills extends Model
{
    protected $table = "job_post_key_skills";
    public $timestamps = false;
    public $primaryKey  = 'jp_skill_id';

    
}
