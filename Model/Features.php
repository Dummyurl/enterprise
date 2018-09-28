<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Features extends Model
{
    protected $table = "features";
    public $primaryKey  = 'id';
    public $timestamps = true;
}
