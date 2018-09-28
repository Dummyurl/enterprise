<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Countries extends Model
{
    protected $table = "countries";
    public $timestamps = false;
    public $primaryKey  = 'id';
}
