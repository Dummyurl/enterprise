<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Jobseeker_details_comments extends Model
{
    protected $table = "jobseeker_details_comments";
    public $timestamps = false;
    public $primaryKey  = 'comment_id';
}
