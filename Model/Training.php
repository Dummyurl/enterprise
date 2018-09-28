<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $table = "training";
    public $timestamps = false;
    public $primaryKey ="training_id";
}
