<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class States extends Model
{
    protected $table = "states";
    public $timestamps = false;
    public $primaryKey  = 'id';
}
