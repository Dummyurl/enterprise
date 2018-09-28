<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Footer_Skills extends Model
{
    protected $table = "footer_skills";
    public $timestamps = false;
    public $primaryKey  = 'skill_id';
}
