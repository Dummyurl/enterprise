<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Benefits extends Model
{
    protected $table = "benefits";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
