<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Package_rules extends Model
{
    protected $table = "package_rules";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
