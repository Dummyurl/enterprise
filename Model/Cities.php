<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{
    protected $table = "cities";
    public $timestamps = false;
    public $primaryKey  = 'id';
}
