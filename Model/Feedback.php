<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = "feedback";
    public $timestamps = false;
    public $primaryKey  = 'feedback_id';
}
