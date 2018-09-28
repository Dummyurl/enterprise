<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Package_content extends Model
{
    protected $table = "package_content";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
